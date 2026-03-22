<?php

namespace App\Http\Controllers;

use App\Models\Purok;
use App\Models\Household;
use App\Models\FamilyMember;
use App\Models\HouseholdHeadTransferRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the authenticated resident's profile (tab: info).
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $tab = $request->get('tab', 'info');
        $canManageOwnFamily = $user->canManageOwnFamily();
        $canManageAnyFamily = $user->canManageAnyFamily();
        $isHead = $canManageOwnFamily;

        $membersPaginator = null;
        $householdMembersTotal = 0;
        $recentlyRemovedMembers = collect();
        $availableLinkedMembers = collect();
        $latestHeadTransferRequest = null;
        $pendingHeadTransferRequest = null;
        $familyHeads = collect();

        // Eager-load family data + purok to avoid N+1
        if ($isHead) {
            $user->load([
                'purokRelation',
            ]);

            $displayMembers = $this->buildDisplayFamilyMembers($user);
            $membersPaginator = $this->paginateDisplayMembers($displayMembers, $request);
            $householdMembersTotal = $displayMembers->count();

            $recentlyRemovedMembers = $user->familyMemberRecords()
                ->onlyTrashed()
                ->where('deleted_at', '>=', now()->subDays(7))
                ->orderByDesc('deleted_at')
                ->limit(8)
                ->get();

            $availableLinkedMembers = User::query()
                ->whereIn('id', $this->eligibleLinkedMemberIdsForHead($user))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $latestHeadTransferRequest = HouseholdHeadTransferRequest::query()
                ->with(['requestedHead'])
                ->where('requested_by', $user->id)
                ->latest('id')
                ->first();

            $householdId = (int) ($user->household_id ?? 0);
            if ($householdId === 0) {
                $householdId = (int) Household::query()
                    ->where('head_id', $user->id)
                    ->value('id');
            }

            if ($householdId > 0) {
                $pendingHeadTransferRequest = HouseholdHeadTransferRequest::query()
                    ->with(['requestedHead'])
                    ->where('household_id', $householdId)
                    ->where('status', HouseholdHeadTransferRequest::STATUS_PENDING)
                    ->latest('id')
                    ->first();
            } elseif ($latestHeadTransferRequest && $latestHeadTransferRequest->status === HouseholdHeadTransferRequest::STATUS_PENDING) {
                $pendingHeadTransferRequest = $latestHeadTransferRequest;
            }
        } else {
            $user->load([
                'headOfFamilyUser.purokRelation',
                'purokRelation',
            ]);

            if ($user->headOfFamilyUser) {
                $displayMembers = $this->buildDisplayFamilyMembers($user->headOfFamilyUser);
                $membersPaginator = $this->paginateDisplayMembers($displayMembers, $request);
                $householdMembersTotal = $displayMembers->count();
            }
        }

        if ($canManageAnyFamily && ! $canManageOwnFamily) {
            $familyHeads = User::query()
                ->whereIn('role', [User::ROLE_RESIDENT, User::ROLE_ADMIN, User::ROLE_STAFF])
                ->where('status', User::STATUS_APPROVED)
                ->where('is_suspended', false)
                ->where('head_of_family', User::HEAD_YES)
                ->whereNull('head_of_family_id')
                ->whereHas('householdAsHead')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        // Residents cannot access the edit tab; redirect to info
        if ($tab === 'edit') {
            return redirect()->route('profile.show', ['tab' => 'info']);
        }

        $puroks = null;

        return view('profile.show', compact(
            'user',
            'tab',
            'puroks',
            'canManageOwnFamily',
            'canManageAnyFamily',
            'membersPaginator',
            'householdMembersTotal',
            'recentlyRemovedMembers',
            'availableLinkedMembers',
            'latestHeadTransferRequest',
            'pendingHeadTransferRequest',
            'familyHeads'
        ));
    }

    /**
     * Build unified household members list for profile display.
     * Includes both family_members records and linked resident users.
     */
    private function buildDisplayFamilyMembers(User $head): Collection
    {
        $head->load([
            'familyMemberRecords' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
            'familyMembers' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
        ]);

        $items = collect();

        $recordLinkedUserIds = $head->familyMemberRecords
            ->pluck('linked_user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($head->familyMemberRecords as $record) {
            $items->push((object) [
                'list_id' => 'family-record-' . $record->id,
                'family_member_id' => $record->id,
                'full_name' => $record->full_name,
                'first_name' => $record->first_name,
                'last_name' => $record->last_name,
                'middle_name' => $record->middle_name,
                'suffix' => $record->suffix,
                'gender' => $record->gender,
                'birthdate' => $record->birthdate,
                'relationship_to_head' => $record->relationship_to_head,
                'contact_number' => $record->contact_number,
                'linked_user_id' => $record->linked_user_id,
                'can_manage' => true,
            ]);
        }

        foreach ($head->familyMembers as $linkedUser) {
            if (in_array((int) $linkedUser->id, $recordLinkedUserIds, true)) {
                continue;
            }

            $items->push((object) [
                'list_id' => 'linked-user-' . $linkedUser->id,
                'family_member_id' => null,
                'full_name' => $linkedUser->full_name,
                'first_name' => $linkedUser->first_name,
                'last_name' => $linkedUser->last_name,
                'middle_name' => $linkedUser->middle_name,
                'suffix' => $linkedUser->suffix,
                'gender' => $linkedUser->gender,
                'birthdate' => $linkedUser->birthdate,
                'relationship_to_head' => $linkedUser->relationship_to_head,
                'contact_number' => $linkedUser->contact_number,
                'linked_user_id' => $linkedUser->id,
                'can_manage' => false,
            ]);
        }

        return $items
            ->sortBy(fn (object $item) => mb_strtolower(trim($item->last_name . ' ' . $item->first_name)))
            ->values();
    }

    private function paginateDisplayMembers(Collection $displayMembers, Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $page = max((int) $request->query('family_page', 1), 1);
        $total = $displayMembers->count();
        $pageItems = $displayMembers->forPage($page, $perPage)->values();

        return (new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'family_page',
            ]
        ))->withQueryString();
    }

    public function submitHeadTransferRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->session()->flash('_family_transfer_form', true);

        if ($user->role !== User::ROLE_RESIDENT) {
            abort(403);
        }

        if (! ($user->head_of_family === 'yes' && $user->head_of_family_id === null)) {
            return back()->withErrors([
                'requested_head_user_id' => 'Only head of family accounts can submit head transfer requests.',
            ]);
        }

        if ($user->status !== User::STATUS_APPROVED || ($user->is_suspended ?? false)) {
            return back()->withErrors([
                'requested_head_user_id' => 'Only active residents can submit head transfer requests.',
            ]);
        }

        $household = $user->householdAsHead()->first();
        if (! $household && $user->household_id) {
            $household = Household::query()->find($user->household_id);
        }
        if (! $household) {
            return back()->withErrors([
                'requested_head_user_id' => 'Unable to resolve your household. Please contact the barangay office.',
            ]);
        }

        $existingPending = HouseholdHeadTransferRequest::query()
            ->where('household_id', $household->id)
            ->where('status', HouseholdHeadTransferRequest::STATUS_PENDING)
            ->exists();
        if ($existingPending) {
            return back()->withErrors([
                'requested_head_user_id' => 'Your household already has a pending transfer request. Please wait for review.',
            ]);
        }

        $hasEligibleLinkedMembers = User::query()
            ->whereIn('id', $this->eligibleLinkedMemberIdsForHead($user))
            ->exists();
        if (! $hasEligibleLinkedMembers) {
            return back()->withErrors([
                'requested_head_user_id' => 'No eligible linked members found for head transfer (must be active linked resident and 18+).',
            ]);
        }

        $eligibleLinkedMemberIds = $this->eligibleLinkedMemberIdsForHead($user);
        $validated = $request->validate([
            'requested_head_user_id' => ['required', 'integer', Rule::in($eligibleLinkedMemberIds)],
            'reason' => ['required', 'string', Rule::in(array_keys(HouseholdHeadTransferRequest::REASON_LABELS))],
            'details' => ['nullable', 'string', 'max:255'],
        ]);

        if (($validated['reason'] ?? null) === 'other') {
            $request->validate([
                'details' => ['required', 'string', 'min:5', 'max:255'],
            ]);
        }

        $requestedHead = User::findOrFail((int) $validated['requested_head_user_id']);
        if ($requestedHead->id === $user->id) {
            return back()->withErrors([
                'requested_head_user_id' => 'You cannot assign yourself as your own head of family.',
            ]);
        }

        if ((int) ($requestedHead->head_of_family_id ?? 0) !== (int) $user->id) {
            $legacyLinkedRecord = FamilyMember::query()
                ->where('head_user_id', $user->id)
                ->where('linked_user_id', $requestedHead->id)
                ->first();

            if (! $legacyLinkedRecord) {
                return back()->withErrors([
                    'requested_head_user_id' => 'You can only transfer to members currently linked under your household.',
                ]);
            }

            if (! empty($requestedHead->head_of_family_id) && (int) $requestedHead->head_of_family_id !== (int) $user->id) {
                return back()->withErrors([
                    'requested_head_user_id' => 'Selected member is currently linked to another head of family.',
                ]);
            }

            $requestedHead->forceFill([
                'head_of_family_id' => $user->id,
                'household_id' => $household->id,
                'head_of_family' => 'no',
                'family_link_status' => 'linked',
                'relationship_to_head' => $requestedHead->relationship_to_head ?: $legacyLinkedRecord->relationship_to_head,
                'house_no' => $user->house_no,
                'purok' => $user->purok,
                'purok_id' => $user->purok_id,
                'resident_type' => $user->resident_type,
            ])->save();
            $requestedHead->refresh();
        }
        if ((int) ($requestedHead->household_id ?? 0) !== (int) $household->id) {
            return back()->withErrors([
                'requested_head_user_id' => 'Selected member must belong to the same household.',
            ]);
        }

        if ($user->head_of_family_id !== null && (int) $user->head_of_family_id === (int) $requestedHead->id) {
            return back()->withErrors([
                'requested_head_user_id' => 'You are already linked to this head of family.',
            ]);
        }

        if (
            $requestedHead->role !== User::ROLE_RESIDENT
            || $requestedHead->head_of_family !== 'no'
            || $requestedHead->status !== User::STATUS_APPROVED
            || ($requestedHead->is_suspended ?? false)
            || ! $this->isOfLegalAgeForHeadTransfer($requestedHead)
        ) {
            return back()->withErrors([
                'requested_head_user_id' => 'Selected linked member is not eligible to become the new head.',
            ]);
        }

        try {
            $transferRequest = HouseholdHeadTransferRequest::create([
                'household_id' => $household->id,
                'current_head_id' => $user->id,
                'new_head_id' => $requestedHead->id,
                'requested_by' => $user->id,
                'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
            ]);
        } catch (QueryException $exception) {
            if (str_contains(strtolower((string) $exception->getMessage()), 'hhtr_pending_household_unique')) {
                return back()->withErrors([
                    'requested_head_user_id' => 'Your household already has a pending transfer request.',
                ]);
            }

            throw $exception;
        }

        NotificationService::notify(
            $user,
            'Head transfer request submitted',
            'Your request to transfer household head to ' . $requestedHead->full_name . ' is now pending verification.',
            'household_transfer',
            $transferRequest->id
        );

        NotificationService::notifyMany(
            $this->headTransferReviewers(),
            'New head transfer request',
            $user->full_name . ' submitted a head transfer request and is waiting for verification.',
            'household_transfer',
            $transferRequest->id
        );

        AuditService::log(
            'family_transfer_request_submitted',
            $user,
            'Submitted head transfer request to ' . $requestedHead->full_name . '. Reason: ' .
                (HouseholdHeadTransferRequest::REASON_LABELS[$validated['reason']] ?? $validated['reason']) .
                (! empty($validated['details']) ? ' - ' . $validated['details'] : '')
        );

        return redirect()->route('profile.show', ['tab' => 'family'])
            ->with('success', 'Head transfer request submitted. Please wait for verification.');
    }

    private function headTransferReviewers(): Collection
    {
        return User::query()
            ->where(function ($query): void {
                $query->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                    ->orWhere(function ($staffQuery): void {
                        $staffQuery->where('role', User::ROLE_STAFF)
                            ->whereHas('staffPermission', fn ($perm) => $perm->where('can_manage_registrations', true));
                    });
            })
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false)
            ->get();
    }

    /**
     * Return IDs of linked users currently eligible to become new household head.
     *
     * @return array<int, int>
     */
    private function eligibleLinkedMemberIdsForHead(User $head): array
    {
        $directLinkedIds = User::query()
            ->where('head_of_family_id', $head->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $legacyLinkedIds = FamilyMember::query()
            ->where('head_user_id', $head->id)
            ->whereNotNull('linked_user_id')
            ->pluck('linked_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $candidateIds = collect(array_merge($directLinkedIds, $legacyLinkedIds))
            ->unique()
            ->filter(fn ($id) => (int) $id !== (int) $head->id)
            ->values()
            ->all();

        if ($candidateIds === []) {
            return [];
        }

        $eligibleIds = User::query()
            ->whereIn('id', $candidateIds)
            ->where('role', User::ROLE_RESIDENT)
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false)
            ->where('head_of_family', 'no')
            ->where(function ($query): void {
                $adultBirthdateCutoff = now()->subYears(18)->toDateString();

                $query->where(function ($birthdateQuery) use ($adultBirthdateCutoff): void {
                    $birthdateQuery->whereNotNull('birthdate')
                        ->whereRaw('date(birthdate) <= ?', [$adultBirthdateCutoff]);
                })->orWhere(function ($fallbackAgeQuery): void {
                    // Fallback only for records without birthdate.
                    $fallbackAgeQuery->whereNull('birthdate')
                        ->where('age', '>=', 18);
                });
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $eligibleIds;
    }

    private function isOfLegalAgeForHeadTransfer(User $user): bool
    {
        if (! empty($user->birthdate)) {
            return \Carbon\Carbon::parse($user->birthdate)->lte(now()->subYears(18));
        }

        return (int) ($user->age ?? 0) >= 18;
    }

    /**
     * Update the authenticated resident's own profile.
     * Residents are not allowed to edit their own profile — only admin/staff may update it.
     */
    public function update(Request $request): RedirectResponse
    {
        abort(403, 'Profile editing is not allowed. Please contact the Barangay Office.');

        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'in:Jr.,Sr.,I,II,III,IV'],
            'house_no' => ['required', 'string', 'max:255'],
            'purok_id' => ['required', 'integer', 'exists:puroks,id'],
            'contact_number' => ['required', 'string', 'regex:/^(\+63|0)?[0-9]{10}$/'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'birthdate' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'string', 'in:single,married,widowed,divorced,separated'],
        ], [
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. +639171234567 or 09171234567).',
        ]);

        // Normalize contact number to +63 format for consistency
        $contact = preg_replace('/[^0-9]/', '', $validated['contact_number']);
        if (str_starts_with($contact, '63') && strlen($contact) === 12) {
            $validated['contact_number'] = '+' . $contact;
        } elseif (str_starts_with($contact, '0') && strlen($contact) === 11) {
            $validated['contact_number'] = '+63' . substr($contact, 1);
        } elseif (strlen($contact) === 10) {
            $validated['contact_number'] = '+63' . $contact;
        }

        // Recalculate age
        $validated['age'] = \Carbon\Carbon::parse($validated['birthdate'])->age;

        // Purok backwards compat
        $purok = Purok::find($validated['purok_id']);
        $validated['purok'] = $purok ? $purok->name : null;

        $user->update($validated);

        return redirect()->route('profile.show', ['tab' => 'info'])
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update resident classification claims and proof uploads from profile.
     */
    public function updateVerification(Request $request, string $type): RedirectResponse
    {
        $user = $request->user();
        $allowed = ['pwd', 'senior'];

        if (! in_array($type, $allowed, true)) {
            abort(404);
        }

        $validated = $request->validate([
            'is_claimed' => ['required', 'string', 'in:yes,no'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'proof.mimes' => 'Proof document must be a JPG, PNG, or PDF file.',
            'proof.max' => 'Proof document must not exceed 2MB.',
        ]);

        $isClaimed = $validated['is_claimed'] === 'yes';
        $pathField = "{$type}_proof_path";
        $statusField = "{$type}_status";
        $isField = "is_{$type}";

        // When claim is removed, clear status and proof entirely.
        if (! $isClaimed) {
            if (! empty($user->{$pathField})) {
                Storage::disk('public')->delete($user->{$pathField});
            }

            $user->forceFill([
                $isField => false,
                $statusField => null,
                $pathField => null,
            ])->save();

            AuditService::log('classification_claim_removed', $user, strtoupper($type) . ' claim removed by resident profile update.');

            return redirect()->route('profile.show', ['tab' => 'verification'])
                ->with('success', strtoupper($type) . ' claim updated.');
        }

        $updates = [
            $isField => true,
        ];

        if ($request->hasFile('proof')) {
            if (! empty($user->{$pathField})) {
                Storage::disk('public')->delete($user->{$pathField});
            }

            $updates[$pathField] = $request->file('proof')->store("classification-proofs/{$type}", 'public');
            $updates[$statusField] = 'pending';
        } else {
            // Keep existing state; if first claim without proof, mark as not_submitted.
            $updates[$statusField] = $user->{$statusField} ?: 'not_submitted';
        }

        $user->forceFill($updates)->save();

        AuditService::log('classification_claim_updated', $user, strtoupper($type) . ' claim updated by resident profile.');

        return redirect()->route('profile.show', ['tab' => 'verification'])
            ->with('success', strtoupper($type) . ' verification details saved.');
    }
}
