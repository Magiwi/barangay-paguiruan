<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Official;
use App\Models\Position;
use App\Models\User;
use App\Services\AuditService;
use App\Support\OfficialCommittees;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfficialController extends Controller
{
    /**
     * List all officials with filters.
     */
    public function index(Request $request): View
    {
        $positions = Position::orderBy('sort_order')->get();

        $eligibleUsers = User::query()
            ->with('position')
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
            ->where('status', User::STATUS_APPROVED)
            ->where(function ($query) {
                $query->whereNull('is_suspended')->orWhere('is_suspended', false);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $activeOfficials = Official::with(['user', 'position'])
            ->currentlyServing()
            ->whereIn('position_id', $positions->pluck('id'))
            ->orderBy('position_id')
            ->orderBy('id')
            ->get()
            ->groupBy('position_id');

        $slotsByPosition = [];
        foreach ($positions as $position) {
            $assigned = $activeOfficials->get($position->id, collect())->values();
            $slots = [];
            for ($i = 0; $i < $position->max_seats; $i++) {
                $slots[] = [
                    'slot_index' => $i + 1,
                    'official' => $assigned->get($i),
                ];
            }
            $slotsByPosition[$position->id] = $slots;
        }

        $staffPositions = $positions->filter(fn ($position) => str_starts_with($position->name, 'Staff '))->values();
        $barangayPositions = $positions->filter(function ($position) {
            return ! str_starts_with($position->name, 'SK ') && ! str_starts_with($position->name, 'Staff ');
        })->values();
        $skPositions = $positions->filter(fn ($position) => str_starts_with($position->name, 'SK '))->values();

        $barangayTerm = $this->resolveCurrentGroupTerm('barangay');
        $skTerm = $this->resolveCurrentGroupTerm('sk');

        return view('admin.officials.index', compact(
            'barangayPositions',
            'skPositions',
            'staffPositions',
            'eligibleUsers',
            'slotsByPosition',
            'barangayTerm',
            'skTerm'
        ));
    }

    /**
     * Assign or update an official in a specific position slot.
     */
    public function assignSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'slot_index' => ['required', 'integer', 'min:1'],
            'official_id' => ['nullable', 'integer', 'exists:officials,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'term_start' => ['nullable', 'date'],
            'term_end' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'photo_removed' => ['nullable', 'boolean'],
        ]);

        $position = Position::findOrFail($validated['position_id']);
        $slotIndex = (int) $validated['slot_index'];

        if ($slotIndex > $position->max_seats) {
            return back()->withErrors(['slot_index' => 'Selected slot does not exist for this position.'])->withInput();
        }

        $user = User::findOrFail($validated['user_id']);
        if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN], true)) {
            return back()->withErrors(['user_id' => 'Only staff or admin users can be assigned as officials.'])->withInput();
        }

        $group = $this->positionGroup($position->name);

        $termStart = $validated['term_start'] ?? null;
        $termEnd = $validated['term_end'] ?? null;

        if ($group !== 'staff') {
            if (! $termStart || ! $termEnd) {
                return back()->withErrors(['term_start' => 'Term start and end are required for Barangay and SK officials.'])->withInput();
            }
            if (Carbon::parse($termEnd)->lt(Carbon::parse($termStart))) {
                return back()->withErrors(['term_end' => 'Term end must be a date after or equal to term start.'])->withInput();
            }

            $termError = $this->validateGroupTermConsistency(
                $group,
                $termStart,
                $termEnd,
                $validated['official_id'] ?? null
            );
            if ($termError) {
                return back()->withErrors(['term_start' => $termError])->withInput();
            }
        } else {
            // Staff assignments are operational slots and are not term-based.
            $termStart = now()->toDateString();
            $termEnd = null;
        }

        $targetOfficial = null;
        if (! empty($validated['official_id'])) {
            $targetOfficial = Official::with(['position', 'user'])->findOrFail($validated['official_id']);
            if ((int) $targetOfficial->position_id !== (int) $position->id) {
                return back()->withErrors(['official_id' => 'Invalid slot assignment target.'])->withInput();
            }
        } else {
            $currentBySlot = Official::with(['position', 'user'])
                ->currentlyServing()
                ->where('position_id', $position->id)
                ->orderBy('id')
                ->get()
                ->values();

            $targetOfficial = $currentBySlot->get($slotIndex - 1);
        }

        $conflict = Official::currentlyServing()
            ->where('user_id', $user->id)
            ->when($targetOfficial, fn ($query) => $query->where('id', '!=', $targetOfficial->id))
            ->first();

        if ($conflict) {
            return back()->withErrors(['user_id' => 'This user is already assigned to another active official slot.'])->withInput();
        }

        if (! $targetOfficial) {
            $activeCount = Official::currentlyServing()->where('position_id', $position->id)->count();
            if ($activeCount >= $position->max_seats) {
                return back()->withErrors(['slot_index' => 'No available seat for this position.'])->withInput();
            }
        }

        $committee = null;
        if (OfficialCommittees::requiresCommittee($position->name)) {
            $request->validate([
                'committee' => ['required', 'string', Rule::in(OfficialCommittees::keys($position->name))],
            ]);
            $committee = $request->input('committee');
            if ($msg = $this->committeeConflictMessage($position, $committee, $targetOfficial?->id)) {
                return back()->withErrors(['committee' => $msg])->withInput();
            }
        }

        $previousUserId = $targetOfficial?->user_id;

        if ($targetOfficial) {
            if ($request->hasFile('photo')) {
                if ($targetOfficial->photo) {
                    Storage::disk('public')->delete($targetOfficial->photo);
                }
                $validated['photo'] = $request->file('photo')->store('officials', 'public');
            } elseif ($request->boolean('photo_removed') && $targetOfficial->photo) {
                Storage::disk('public')->delete($targetOfficial->photo);
                $validated['photo'] = null;
            }

            $targetOfficial->update([
                'user_id' => $user->id,
                'position_id' => $position->id,
                'term_start' => $termStart,
                'term_end' => $termEnd,
                'is_active' => true,
                'photo' => $validated['photo'] ?? $targetOfficial->photo,
                'committee' => $committee,
            ]);
            $official = $targetOfficial;
        } else {
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('officials', 'public');
            }

            $official = Official::create([
                'user_id' => $user->id,
                'position_id' => $position->id,
                'term_start' => $termStart,
                'term_end' => $termEnd,
                'is_active' => true,
                'photo' => $validated['photo'] ?? null,
                'committee' => $committee,
            ]);
        }

        $user->forceFill([
            'position_id' => $position->id,
            'position_title' => $position->name,
        ])->save();

        if ($previousUserId && $previousUserId !== $user->id) {
            $previousUser = User::find($previousUserId);
            if ($previousUser && ! Official::currentlyServing()->where('user_id', $previousUser->id)->exists()) {
                $previousUser->forceFill([
                    'position_id' => null,
                    'position_title' => null,
                ])->save();
            }
        }

        AuditService::log(
            'official_slot_assigned',
            $official,
            "Assigned {$user->full_name} to {$position->name} (slot {$slotIndex})"
        );

        return back()->with('success', "{$position->name} slot {$slotIndex} updated successfully.");
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $positions = Position::orderBy('sort_order')->get();
        $eligibleUsers = User::with('position')
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.officials.create', compact('positions', 'eligibleUsers'));
    }

    /**
     * Store a new official record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'term_start' => ['required', 'date'],
            'term_end' => ['nullable', 'date', 'after:term_start'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'committee' => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN], true)) {
            return back()->withErrors(['user_id' => 'Only staff or admin users can be assigned as officials.'])->withInput();
        }

        $position = Position::findOrFail($validated['position_id']);

        $committee = null;
        if (OfficialCommittees::requiresCommittee($position->name)) {
            $request->validate([
                'committee' => ['required', 'string', Rule::in(OfficialCommittees::keys($position->name))],
            ]);
            $committee = $request->input('committee');
            if ($this->committeeConflictMessage($position, $committee, null)) {
                return back()->withErrors(['committee' => 'Another active official in this position already holds that committee.'])->withInput();
            }
        }

        if (! $position->hasAvailableSeat()) {
            return back()->withErrors([
                'position_id' => "Maximum number of {$position->name} ({$position->max_seats}) reached.",
            ])->withInput();
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('officials', 'public');
        }
        unset($validated['photo_removed']);

        $validated['committee'] = $committee;

        $official = Official::create($validated);

        // Sync position to user record
        $user->forceFill([
            'position_id' => $position->id,
            'position_title' => $position->name,
        ])->save();

        AuditService::log('official_appointed', $official, "Appointed {$user->full_name} as {$position->name}");

        return redirect()->route('admin.officials.index')
            ->with('success', "{$user->full_name} appointed as {$position->name}.");
    }

    /**
     * Show edit form.
     */
    public function edit(Official $official): View
    {
        $official->load(['user', 'position']);
        $positions = Position::orderBy('sort_order')->get();

        return view('admin.officials.edit', compact('official', 'positions'));
    }

    /**
     * Update an official record.
     */
    public function update(Request $request, Official $official): RedirectResponse
    {
        $validated = $request->validate([
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'term_start' => ['required', 'date'],
            'term_end' => ['nullable', 'date', 'after:term_start'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'committee' => ['nullable', 'string', 'max:64'],
        ]);

        $position = Position::findOrFail($validated['position_id']);

        $committee = null;
        if (OfficialCommittees::requiresCommittee($position->name)) {
            $request->validate([
                'committee' => ['required', 'string', Rule::in(OfficialCommittees::keys($position->name))],
            ]);
            $committee = $request->input('committee');
            if ($this->committeeConflictMessage($position, $committee, $official->id)) {
                return back()->withErrors(['committee' => 'Another active official in this position already holds that committee.'])->withInput();
            }
        }

        if ($validated['position_id'] != $official->position_id) {
            if (! $position->hasAvailableSeat($official->user_id)) {
                return back()->withErrors([
                    'position_id' => "Maximum number of {$position->name} ({$position->max_seats}) reached.",
                ])->withInput();
            }
        }

        if ($request->hasFile('photo')) {
            if ($official->photo) {
                Storage::disk('public')->delete($official->photo);
            }
            $validated['photo'] = $request->file('photo')->store('officials', 'public');
        } elseif ($request->boolean('photo_removed') && $official->photo) {
            Storage::disk('public')->delete($official->photo);
            $validated['photo'] = null;
        }
        unset($validated['photo_removed']);

        $validated['committee'] = $committee;

        $official->update($validated);

        // Sync position to user record
        $official->user->forceFill([
            'position_id' => $position->id,
            'position_title' => $position->name,
        ])->save();

        // Check if term expired after update
        if ($official->shouldRevokeAccess()) {
            $this->revokePermissions($official);
        }

        AuditService::log('official_updated', $official, "Updated official record for {$official->user->full_name}");

        return redirect()->route('admin.officials.index')
            ->with('success', 'Official record updated.');
    }

    /**
     * Toggle active status for an official.
     */
    public function toggleActive(Official $official): RedirectResponse
    {
        $wasActive = $official->is_active;
        $official->forceFill(['is_active' => ! $wasActive])->save();

        if ($wasActive) {
            // Deactivated — revoke permissions
            $this->revokePermissions($official);
            AuditService::log('official_deactivated', $official, "Deactivated {$official->user->full_name} as {$official->position->name}");
        } else {
            // Reactivated — sync position back
            $official->user->forceFill([
                'position_id' => $official->position_id,
                'position_title' => $official->position->name,
            ])->save();
            AuditService::log('official_activated', $official, "Reactivated {$official->user->full_name} as {$official->position->name}");
        }

        $label = $wasActive ? 'deactivated' : 'reactivated';

        return back()->with('success', "Official {$label} successfully.");
    }

    /**
     * Revoke permissions for an official's user account.
     * Safe: does NOT auto-downgrade admin role.
     */
    private function revokePermissions(Official $official): void
    {
        $user = $official->user;

        if ($user->role === User::ROLE_STAFF) {
            // Zero out all module permissions
            $user->staffPermission()?->update([
                'can_manage_registrations' => false,
                'can_manage_blotter' => false,
                'can_manage_announcements' => false,
                'can_manage_complaints' => false,
                'can_manage_reports' => false,
            ]);

            // Remove position assignment
            $user->forceFill([
                'position_id' => null,
                'position_title' => null,
            ])->save();

            // Downgrade staff to resident
            $user->forceFill(['role' => User::ROLE_RESIDENT])->save();
            $user->staffPermission()?->delete();

            AuditService::log('permissions_revoked', $user, 'Permissions revoked and role downgraded to resident (official deactivated/expired)');

        } elseif ($user->role === User::ROLE_ADMIN) {
            // Do NOT downgrade admin — only clear position, log warning
            $user->forceFill([
                'position_id' => null,
                'position_title' => null,
            ])->save();

            AuditService::log('admin_term_ended', $user, 'Admin official term ended/deactivated — position cleared but role preserved (manual review required)');
        }
    }

    /**
     * Static method for use by scheduled command.
     * Finds all expired-but-active officials and revokes access.
     */
    public static function processExpiredOfficials(): int
    {
        $expiredOfficials = Official::expired()->with(['user', 'position'])->get();
        $count = 0;

        foreach ($expiredOfficials as $official) {
            $official->forceFill(['is_active' => false])->save();
            (new static)->revokePermissions($official);
            AuditService::log('official_auto_expired', $official, "Auto-expired {$official->user->full_name} — term ended {$official->term_end->format('M d, Y')}");
            $count++;
        }

        return $count;
    }

    private function resolveCurrentGroupTerm(string $group): array
    {
        // Prefer officials with a full term window so appointed/incomplete rows do not clear the group pickers.
        $official = Official::with('position')
            ->currentlyServing()
            ->whereNotNull('term_start')
            ->whereNotNull('term_end')
            ->whereHas('position', function ($query) use ($group) {
                if ($group === 'sk') {
                    $query->where('name', 'like', 'SK %');
                } else {
                    $query->where('name', 'not like', 'SK %')
                        ->where('name', 'not like', 'Staff %');
                }
            })
            ->orderBy('term_start')
            ->first();

        return [
            'term_start' => $official?->term_start?->toDateString(),
            'term_end' => $official?->term_end?->toDateString(),
        ];
    }

    private function positionGroup(string $positionName): string
    {
        if (str_starts_with($positionName, 'Staff ')) {
            return 'staff';
        }

        return str_starts_with($positionName, 'SK ') ? 'sk' : 'barangay';
    }

    /**
     * @return string|null Error message if the committee is already taken by another active holder of this position.
     */
    private function committeeConflictMessage(Position $position, string $committeeKey, ?int $ignoreOfficialId): ?string
    {
        $exists = Official::query()
            ->currentlyServing()
            ->where('position_id', $position->id)
            ->where('committee', $committeeKey)
            ->when($ignoreOfficialId, fn ($query) => $query->where('id', '!=', $ignoreOfficialId))
            ->exists();

        return $exists
            ? 'Another active official in this position already holds that committee.'
            : null;
    }

    private function validateGroupTermConsistency(string $group, string $termStart, string $termEnd, ?int $ignoreOfficialId = null): ?string
    {
        $start = Carbon::parse($termStart)->toDateString();
        $end = Carbon::parse($termEnd)->toDateString();

        $existing = Official::with('position')
            ->currentlyServing()
            ->whereNotNull('term_start')
            ->whereNotNull('term_end')
            ->whereHas('position', function ($query) use ($group) {
                if ($group === 'sk') {
                    $query->where('name', 'like', 'SK %');
                } else {
                    $query->where('name', 'not like', 'SK %')
                        ->where('name', 'not like', 'Staff %');
                }
            })
            ->when($ignoreOfficialId, fn ($query) => $query->where('id', '!=', $ignoreOfficialId))
            ->first();

        if (! $existing) {
            return null;
        }

        $existingStart = $existing->term_start->toDateString();
        $existingEnd = $existing->term_end->toDateString();
        if ($existingStart !== $start || $existingEnd !== $end) {
            $groupLabel = $group === 'sk' ? 'SK' : 'Barangay';

            return "{$groupLabel} officials must use the same shared term period.";
        }

        return null;
    }
}
