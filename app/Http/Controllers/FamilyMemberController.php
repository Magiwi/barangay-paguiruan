<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FamilyMemberController extends Controller
{
    private const RESTORE_WINDOW_DAYS = 7;

    /**
     * Normalize PH mobile numbers to +63 format.
     */
    private function normalizeContactNumber(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, '+63')) {
            return $raw;
        }

        if (str_starts_with($raw, '0')) {
            return '+63' . substr($raw, 1);
        }

        return $raw;
    }

    /**
     * Resolve an existing household for a valid head.
     * Never auto-creates household records to protect data integrity.
     */
    private function resolveHeadHousehold(User $head): Household
    {
        $household = $head->householdAsHead;

        if (! $household) {
            throw ValidationException::withMessages([
                'head_of_family_id' => 'Selected household head has no valid household record.',
            ]);
        }

        if (($head->purok ?? '') !== '' && $household->purok !== $head->purok) {
            $household->update(['purok' => $head->purok]);
        }

        return $household;
    }

    /**
     * Validate form and enforce age-based contact rules.
     */
    private function validateMemberPayload(Request $request, User $head): array
    {
        $allowedRelationships = [
            'son',
            'daughter',
            'spouse',
            'father',
            'mother',
            'brother',
            'sister',
            'grandfather',
            'grandmother',
            'uncle',
            'aunt',
            'cousin',
            'nephew',
            'niece',
            'guardian',
            'boarder',
            'helper',
            'other',
        ];

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'in:Jr.,Sr.,I,II,III,IV'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'contact_number' => ['nullable', 'regex:/^(\+63|0)9[0-9]{9}$/'],
            'relationship_to_head' => ['required', 'string', Rule::in($allowedRelationships)],
            'link_existing_if_duplicate' => ['nullable', 'boolean'],
        ], [
            'contact_number.regex' => 'Contact number must start with 09 or +639 and contain a valid PH mobile format.',
            'relationship_to_head.required' => 'Please specify the relationship to head of family.',
        ]);

        $age = Carbon::parse($validated['birthdate'])->age;
        $validated['age'] = $age;
        $validated['relationship_to_head'] = strtolower(trim((string) $validated['relationship_to_head']));
        $headAge = $head->birthdate ? Carbon::parse($head->birthdate)->age : (int) $head->age;

        $validated['contact_number'] = $this->normalizeContactNumber($validated['contact_number'] ?? null);

        if ($age >= 18 && empty($validated['contact_number'])) {
            throw ValidationException::withMessages([
                'contact_number' => 'Contact number is required for adults.',
            ]);
        }

        // Always clear contact for children below 10.
        if ($age < 10) {
            $validated['contact_number'] = null;
        }

        if ($age < 18 && in_array($validated['relationship_to_head'], ['spouse', 'father', 'mother', 'uncle', 'aunt'], true)) {
            throw ValidationException::withMessages([
                'relationship_to_head' => 'Invalid relationship based on age.',
            ]);
        }

        if ($validated['relationship_to_head'] === 'spouse' && $age < 18) {
            throw ValidationException::withMessages([
                'relationship_to_head' => 'Spouse must be 18 or older.',
            ]);
        }

        if (in_array($validated['relationship_to_head'], ['father', 'mother'], true) && $age < 25) {
            throw ValidationException::withMessages([
                'relationship_to_head' => 'Parent role requires at least 25 years old.',
            ]);
        }

        if (in_array($validated['relationship_to_head'], ['grandfather', 'grandmother'], true) && $age < 40) {
            throw ValidationException::withMessages([
                'relationship_to_head' => 'Grandparent role requires at least 40 years old.',
            ]);
        }

        if (in_array($validated['relationship_to_head'], ['uncle', 'aunt'], true) && $age < 21) {
            throw ValidationException::withMessages([
                'relationship_to_head' => 'Uncle/Aunt must be at least 21 years old.',
            ]);
        }

        if (in_array($validated['relationship_to_head'], ['son', 'daughter'], true) && $headAge > 0) {
            if ($age >= $headAge) {
                throw ValidationException::withMessages([
                    'relationship_to_head' => 'Child must be younger than head of family.',
                ]);
            }

            if (($headAge - $age) < 12) {
                throw ValidationException::withMessages([
                    'relationship_to_head' => 'Invalid age gap between parent and child.',
                ]);
            }
        }

        return $validated;
    }

    /**
     * Find probable duplicate resident record by identity fields.
     */
    private function findPotentialDuplicateMember(array $validated): ?User
    {
        $firstName = mb_strtolower(trim((string) $validated['first_name']));
        $lastName = mb_strtolower(trim((string) $validated['last_name']));
        $middleName = mb_strtolower(trim((string) ($validated['middle_name'] ?? '')));
        $birthdate = (string) $validated['birthdate'];
        $suffix = trim((string) ($validated['suffix'] ?? ''));
        $contact = trim((string) ($validated['contact_number'] ?? ''));

        return User::query()
            ->where('role', User::ROLE_RESIDENT)
            ->whereDate('birthdate', $birthdate)
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [$firstName])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [$lastName])
            ->whereRaw('LOWER(TRIM(COALESCE(middle_name, ""))) = ?', [$middleName])
            ->whereRaw('TRIM(COALESCE(suffix, "")) = ?', [$suffix])
            ->when($contact !== '', function ($query) use ($contact): void {
                $query->where(function ($contactQuery) use ($contact): void {
                    $digits = preg_replace('/[^0-9]/', '', $contact);
                    if (str_starts_with($digits, '63')) {
                        $zeroFormat = '0' . substr($digits, 2);
                    } elseif (str_starts_with($digits, '0')) {
                        $zeroFormat = $digits;
                    } else {
                        $zeroFormat = '0' . $digits;
                    }
                    $plusFormat = '+63' . ltrim(substr($zeroFormat, 1), '0');

                    $contactQuery
                        ->where('contact_number', $contact)
                        ->orWhere('contact_number', $zeroFormat)
                        ->orWhere('contact_number', $plusFormat);
                });
            })
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();
    }

    /**
     * Resolve which head/household to use for create flow.
     * Head-only: actor must be an eligible household head.
     */
    private function resolveCreateHead(Request $request): User
    {
        $actor = $request->user();
        $this->authorize('create', FamilyMember::class);

        if ($actor->canManageOwnFamily()) {
            return $actor;
        }

        abort(403);
    }

    /**
     * Ensure the member belongs to the authenticated head's family.
     */
    private function authorizeMember(Request $request, FamilyMember $member, string $ability = 'update'): User
    {
        $head = $request->user();
        $this->authorize($ability, $member);

        return $head;
    }

    /**
     * Store a new family member under the authenticated head.
     */
    public function store(Request $request): RedirectResponse
    {
        // Flash marker so Blade knows to re-open the add modal on validation errors
        $request->session()->flash('_family_form', true);

        $head = $this->resolveCreateHead($request);
        $household = $this->resolveHeadHousehold($head);

        $validated = $this->validateMemberPayload($request, $head);
        $linkExisting = (bool) ($validated['link_existing_if_duplicate'] ?? false);
        unset($validated['link_existing_if_duplicate']);

        $duplicate = $this->findPotentialDuplicateMember($validated);
        if ($duplicate) {
            if ((int) $duplicate->head_of_family_id === (int) $head->id) {
                return back()->withErrors([
                    'first_name' => 'A matching resident is already linked in your family. Duplicate record was prevented.',
                ])->withInput();
            }

            if ($linkExisting) {
                $member = FamilyMember::create([
                    'head_user_id' => $head->id,
                    'household_id' => $household->id,
                    'linked_user_id' => $duplicate->id,
                    'first_name' => $duplicate->first_name,
                    'middle_name' => $duplicate->middle_name,
                    'last_name' => $duplicate->last_name,
                    'suffix' => $duplicate->suffix,
                    'birthdate' => $duplicate->birthdate,
                    'age' => $duplicate->birthdate ? $duplicate->birthdate->age : $duplicate->age,
                    'gender' => $duplicate->gender,
                    'contact_number' => $duplicate->contact_number,
                    'relationship_to_head' => $validated['relationship_to_head'],
                    'house_no' => $head->house_no,
                    'purok' => $head->purok,
                    'purok_id' => $head->purok_id,
                    'resident_type' => $head->resident_type,
                ]);

                AuditService::log(
                    'family_member_linked_existing',
                    $head,
                    "Head {$head->full_name} linked existing resident {$duplicate->full_name} as a family member record (family_member_id: {$member->id}) instead of creating a duplicate."
                );

                return redirect()->route('profile.show', ['tab' => 'family'])
                    ->with('success', "Existing resident {$duplicate->full_name} was linked to your family.");
            }

            return back()->withErrors([
                'first_name' => "Possible duplicate found: {$duplicate->full_name}. Tick \"Link existing match\" to link the existing resident record.",
            ])->withInput();
        }

        // Auto-assign fields — never from user input
        $validated['head_user_id'] = $head->id;
        $validated['household_id'] = $household->id;
        // Inherit address/purok from head
        $validated['house_no'] = $head->house_no;
        $validated['purok'] = $head->purok;
        $validated['purok_id'] = $head->purok_id;
        $validated['resident_type'] = $head->resident_type;

        // Create a non-account family member record.
        $member = FamilyMember::create($validated);

        AuditService::log(
            'family_member_added',
            $head,
            "Head {$head->full_name} added family member {$member->full_name} ({$member->relationship_to_head})."
        );

        return redirect()->route('profile.show', ['tab' => 'family'])
            ->with('success', $validated['first_name'] . ' has been added to your family.');
    }

    /**
     * Update an existing family member's information.
     */
    public function update(Request $request, FamilyMember $member): RedirectResponse
    {
        $head = $this->authorizeMember($request, $member, 'update');
        $household = $this->resolveHeadHousehold($head);
        $originalRelationship = $member->relationship_to_head;
        $originalContact = $member->contact_number;

        $validated = $this->validateMemberPayload($request, $head);
        $validated['head_user_id'] = $head->id;
        $validated['household_id'] = $household->id;
        // Keep linked members aligned to the head household address profile.
        $validated['house_no'] = $head->house_no;
        $validated['purok'] = $head->purok;
        $validated['purok_id'] = $head->purok_id;
        $validated['resident_type'] = $head->resident_type;

        $member->update($validated);

        $changes = [];
        if ($member->wasChanged('relationship_to_head')) {
            $changes[] = "relationship {$originalRelationship} -> {$member->relationship_to_head}";
        }
        if ($member->wasChanged('contact_number')) {
            $changes[] = "contact {$originalContact} -> {$member->contact_number}";
        }
        $changeSummary = $changes === [] ? 'profile fields updated' : implode('; ', $changes);

        AuditService::log(
            'family_member_updated',
            $head,
            "Head {$head->full_name} updated family member {$member->full_name}: {$changeSummary}."
        );

        return redirect()->route('profile.show', ['tab' => 'family'])
            ->with('success', $validated['first_name'] . '\'s information has been updated.');
    }

    /**
     * Remove a family member from the head's household.
     */
    public function destroy(Request $request, FamilyMember $member): RedirectResponse
    {
        $this->authorizeMember($request, $member, 'delete');

        $name = $member->full_name;

        // Soft-delete for recoverability within the restore window.
        $member->delete();

        AuditService::log(
            'family_member_removed',
            $request->user(),
            "Head {$request->user()->full_name} removed {$name} from household (soft delete)."
        );

        return redirect()->route('profile.show', ['tab' => 'family'])
            ->with('success', $name . ' has been removed from your family. You can restore this within ' . self::RESTORE_WINDOW_DAYS . ' days.');
    }

    /**
     * Restore a recently removed family member within restore window.
     */
    public function restore(Request $request, int $member): RedirectResponse
    {
        $head = $request->user();
        $this->authorize('create', FamilyMember::class);

        $memberRecord = FamilyMember::withTrashed()
            ->where('id', $member)
            ->where('head_user_id', $head->id)
            ->firstOrFail();
        $this->authorize('restore', $memberRecord);

        if ($memberRecord->deleted_at === null) {
            return redirect()->route('profile.show', ['tab' => 'family'])
                ->with('success', "{$memberRecord->full_name} is already active in your family.");
        }

        $restoreDeadline = $memberRecord->deleted_at->copy()->addDays(self::RESTORE_WINDOW_DAYS);
        if (now()->greaterThan($restoreDeadline)) {
            return redirect()->route('profile.show', ['tab' => 'family'])
                ->withErrors(['family_restore' => 'Restore window expired for this family member.']);
        }

        $memberRecord->restore();

        AuditService::log(
            'family_member_restored',
            $head,
            "Head {$head->full_name} restored family member {$memberRecord->full_name}."
        );

        return redirect()->route('profile.show', ['tab' => 'family'])
            ->with('success', "{$memberRecord->full_name} has been restored to your family.");
    }
}
