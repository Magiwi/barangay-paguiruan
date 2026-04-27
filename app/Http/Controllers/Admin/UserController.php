<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\HouseholdHeadTransferLog;
use App\Models\Position;
use App\Models\PositionChangeLog;
use App\Models\Purok;
use App\Models\ResidentMergeLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HeadTransferService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UserController extends Controller
{
    public function __construct(
        private readonly HeadTransferService $headTransferService
    ) {}

    /** Maximum number of users per role. */
    public const MAX_ADMINS = 2;

    public const MAX_STAFF = 8;

    private const HOUSEHOLD_RELATIONSHIP_OPTIONS = [
        'spouse',
        'partner',
        'father',
        'mother',
        'son',
        'daughter',
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

    private const TRANSFER_REASON_CODES = [
        'correction_error',
        'residence_update',
        'head_unavailable',
        'duplicate_cleanup',
        'other',
    ];

    private const POSITION_REASON_CODES = [
        'organizational_update',
        'seat_reallocation',
        'role_reassignment',
        'compliance_adjustment',
        'other',
    ];

    /**
     * Tables/columns whose ownership should be transferred during duplicate merge.
     *
     * @return array<int, array{name:string,column:string,label:string}>
     */
    private function duplicateMergeTables(): array
    {
        return [
            ['name' => 'certificate_requests', 'column' => 'user_id', 'label' => 'Certificate requests'],
            ['name' => 'permits', 'column' => 'user_id', 'label' => 'Permits'],
            ['name' => 'issue_reports', 'column' => 'user_id', 'label' => 'Issue reports'],
            ['name' => 'blotter_requests', 'column' => 'user_id', 'label' => 'Blotter requests'],
            ['name' => 'user_notifications', 'column' => 'user_id', 'label' => 'Notifications'],
            ['name' => 'sms_logs', 'column' => 'user_id', 'label' => 'SMS logs'],
            ['name' => 'login_activities', 'column' => 'user_id', 'label' => 'Login activities'],
            ['name' => 'blotters', 'column' => 'complainant_user_id', 'label' => 'Blotter complainant links'],
        ];
    }

    /**
     * Count records that would be transferred for one resident.
     *
     * @return array{total:int,breakdown:array<string,int>}
     */
    private function transferCountsForResident(int $residentId): array
    {
        $breakdown = [];
        $total = 0;

        foreach ($this->duplicateMergeTables() as $table) {
            $count = DB::table($table['name'])->where($table['column'], $residentId)->count();
            $breakdown[$table['label']] = $count;
            $total += $count;
        }

        return [
            'total' => $total,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Canonical identity tuple used for strict duplicate-merge safety checks.
     *
     * @return array{first_name:string,middle_name:string,last_name:string,suffix:string,birthdate:string}
     */
    private function residentIdentity(User $user): array
    {
        return [
            'first_name' => mb_strtolower(trim((string) $user->first_name)),
            'middle_name' => mb_strtolower(trim((string) ($user->middle_name ?? ''))),
            'last_name' => mb_strtolower(trim((string) $user->last_name)),
            'suffix' => trim((string) ($user->suffix ?? '')),
            'birthdate' => optional($user->birthdate)->toDateString() ?: '',
        ];
    }

    /**
     * Display a listing of users for role management.
     */
    public function index()
    {
        $query = User::query()
            ->where('role', '!=', User::ROLE_SUPER_ADMIN)
            ->orderBy('last_name')
            ->orderBy('first_name');

        // Search by name, email, or contact
        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($purokId = request('purok_id')) {
            $query->where('purok_id', $purokId);
        }

        if ($role = request('role')) {
            $query->where('role', $role);
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($residentType = request('resident_type')) {
            $query->where('resident_type', $residentType);
        }

        // Head of Family filter
        if ($headFilter = request('head_of_family')) {
            $query->where('head_of_family', $headFilter);
        }

        // Classification filters
        if (request('is_pwd') === 'yes') {
            $query->where('is_pwd', true);
        } elseif (request('is_pwd') === 'no') {
            $query->where('is_pwd', false);
        }

        if (request('is_senior') === 'yes') {
            $query->where('is_senior', true);
        } elseif (request('is_senior') === 'no') {
            $query->where('is_senior', false);
        }

        // Verification status filter (applies to PWD or Senior status)
        if ($verificationStatus = request('verification_status')) {
            $query->where(function ($q) use ($verificationStatus) {
                $q->where(function ($sub) use ($verificationStatus) {
                    $sub->where('is_pwd', true)->where('pwd_status', $verificationStatus);
                })->orWhere(function ($sub) use ($verificationStatus) {
                    $sub->where('is_senior', true)->where('senior_status', $verificationStatus);
                });
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $puroks = Purok::orderBy('name')->get();

        return view('admin.residents.index', compact('users', 'puroks'));
    }

    /**
     * Update the role for a given user.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        // Prevent admin from changing their own role
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'role' => 'You cannot change your own role.',
            ]);
        }

        // Protect super admin role from admin-side role reassignment.
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            return back()->withErrors([
                'role' => 'Super admin role can only be managed via secure CLI command.',
            ]);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:resident,staff,admin'],
        ]);

        $newRole = $validated['role'];

        // Prevent removing the last admin
        if ($user->role === User::ROLE_ADMIN && $newRole !== User::ROLE_ADMIN) {
            $adminCount = User::where('role', User::ROLE_ADMIN)->count();

            if ($adminCount <= 1) {
                return back()->withErrors([
                    'role' => 'You cannot remove the last admin from the system.',
                ]);
            }
        }

        // Enforce admin seat limit
        if ($newRole === User::ROLE_ADMIN && $user->role !== User::ROLE_ADMIN) {
            $currentAdmins = User::where('role', User::ROLE_ADMIN)->count();

            if ($currentAdmins >= self::MAX_ADMINS) {
                return back()->withErrors([
                    'role' => 'Maximum number of admins ('.self::MAX_ADMINS.') reached.',
                ]);
            }
        }

        // Enforce staff seat limit
        if ($newRole === User::ROLE_STAFF && $user->role !== User::ROLE_STAFF) {
            $currentStaff = User::where('role', User::ROLE_STAFF)->count();

            if ($currentStaff >= self::MAX_STAFF) {
                return back()->withErrors([
                    'role' => 'Maximum number of staff ('.self::MAX_STAFF.') reached.',
                ]);
            }
        }

        $oldRole = $user->role;
        $user->forceFill(['role' => $newRole])->save();

        // Clean up when leaving staff/admin: remove permissions and position
        if ($oldRole !== $newRole) {
            if (in_array($oldRole, [User::ROLE_STAFF, User::ROLE_ADMIN], true)
                && $newRole === User::ROLE_RESIDENT) {
                $user->staffPermission()?->delete();
                $user->forceFill(['position_id' => null, 'position_title' => null])->save();
            }
        }

        AuditService::log('role_updated', $user, "Changed role from {$oldRole} to {$newRole}");

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * Show a resident's full profile (admin/staff).
     */
    public function show(User $user)
    {
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            abort(404);
        }

        $user->load([
            'household.head',
            'household.members',
            'headOfFamilyUser',
            'familyMembers' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
            'purokRelation',
            'staffPermission',
            'position',
        ]);

        // Provide available heads for the manual family-link dropdown
        $familyHeads = collect();
        if (! $user->isHeadOfFamily()) {
            $familyHeads = User::where('head_of_family', 'yes')
                ->where('role', User::ROLE_RESIDENT)
                ->where('status', User::STATUS_APPROVED)
                ->where('id', '!=', $user->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        $positionDisplayNames = [
            'Barangay Chairman' => 'Punong Barangay',
            'Kagawad' => 'Barangay Kagawad',
            'Barangay Secretary' => 'Secretary',
            'Barangay Treasurer' => 'Treasurer',
        ];
        $positionSeedDefaults = [
            ['name' => 'Barangay Chairman', 'max_seats' => 1, 'sort_order' => 1],
            ['name' => 'Kagawad', 'max_seats' => 7, 'sort_order' => 5],
            ['name' => 'SK Chairman', 'max_seats' => 1, 'sort_order' => 6],
            ['name' => 'SK Kagawad', 'max_seats' => 7, 'sort_order' => 7],
            ['name' => 'Barangay Secretary', 'max_seats' => 1, 'sort_order' => 2],
            ['name' => 'Barangay Treasurer', 'max_seats' => 1, 'sort_order' => 3],
            ['name' => 'Barangay Investigator', 'max_seats' => 1, 'sort_order' => 4],
        ];
        foreach ($positionSeedDefaults as $defaultPosition) {
            Position::updateOrCreate(
                ['name' => $defaultPosition['name']],
                $defaultPosition
            );
        }
        $positionGroups = [
            'Elected' => [
                'Barangay Chairman',
                'Kagawad',
                'SK Chairman',
                'SK Kagawad',
            ],
            'Appointed' => [
                'Barangay Secretary',
                'Barangay Treasurer',
                'Barangay Investigator',
            ],
        ];
        $allowedPositionNames = array_values(array_unique(array_merge(
            $positionGroups['Elected'],
            $positionGroups['Appointed'],
        )));
        $positions = Position::query()
            ->whereIn('name', $allowedPositionNames)
            ->orderBy('sort_order')
            ->get();
        $headTransferLogs = HouseholdHeadTransferLog::query()
            ->with(['oldHeadUser', 'newHeadUser', 'changedByUser'])
            ->where('resident_user_id', $user->id)
            ->latest()
            ->limit(15)
            ->get();
        $transferReasonOptions = HouseholdHeadTransferLog::REASON_LABELS;
        $positionChangeLogs = PositionChangeLog::query()
            ->with(['oldPosition', 'newPosition', 'changedByUser'])
            ->where('resident_user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();
        $positionReasonOptions = PositionChangeLog::REASON_LABELS;

        return view('admin.residents.show', compact(
            'user',
            'familyHeads',
            'positions',
            'headTransferLogs',
            'transferReasonOptions',
            'positionDisplayNames',
            'positionGroups',
            'positionChangeLogs',
            'positionReasonOptions'
        ));
    }

    /**
     * Show form for editing a resident's core information.
     */
    public function edit(User $user)
    {
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            abort(404);
        }

        $householdHeads = User::orderBy('last_name')
            ->orderBy('first_name')
            ->where('role', User::ROLE_RESIDENT)
            ->where('id', '!=', $user->id)
            ->whereHas('householdAsHead')
            ->get();

        $puroks = Purok::active()->orderBy('name')->get();

        if ($user->role === User::ROLE_STAFF) {
            $user->load('staffPermission');

            if (! $user->staffPermission) {
                $user->staffPermission()->create([
                    'can_manage_blotter' => false,
                    'can_manage_announcements' => false,
                    'can_manage_complaints' => false,
                    'can_manage_reports' => false,
                ]);
                $user->load('staffPermission');
            }
        }

        return view('admin.residents.edit', compact('user', 'householdHeads', 'puroks'));
    }

    /**
     * Update a resident's core profile information.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            // Personal
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:10'],
            // Address
            'house_no' => ['required', 'string', 'max:255'],
            'purok_id' => ['required', 'integer', 'exists:puroks,id'],
            // Demographics
            'contact_number' => ['required', 'string', 'regex:/^(\+63|0)?9[0-9]{9}$/'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'birthdate' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'string', 'in:single,married,widowed,divorced,separated'],
            'resident_type' => ['required', 'string', 'in:permanent,non-permanent'],
            // Non-permanent permanent-address fields
            'permanent_house_no' => ['nullable', 'string', 'max:255'],
            'permanent_street' => ['nullable', 'string', 'max:255'],
            'permanent_region' => ['nullable', 'string', 'max:255'],
            'permanent_barangay' => ['nullable', 'string', 'max:255'],
            'permanent_city' => ['nullable', 'string', 'max:255'],
            'permanent_province' => ['nullable', 'string', 'max:255'],
            // Household
            'is_head' => ['nullable', 'boolean'],
            'household_id' => ['nullable', 'integer', 'exists:households,id'],
            'relationship_to_head' => ['nullable', 'string', Rule::in(self::HOUSEHOLD_RELATIONSHIP_OPTIONS)],
        ];

        if ($user->role === User::ROLE_STAFF) {
            $rules['can_manage_registrations'] = ['nullable', 'boolean'];
            $rules['can_manage_blotter'] = ['nullable', 'boolean'];
            $rules['can_manage_announcements'] = ['nullable', 'boolean'];
            $rules['can_manage_complaints'] = ['nullable', 'boolean'];
            $rules['can_manage_reports'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules, [
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. +639171234567 or 09171234567).',
        ]);

        if (($validated['resident_type'] ?? null) === 'non-permanent') {
            $request->validate([
                'permanent_house_no' => ['required', 'string', 'max:255'],
                'permanent_street' => ['required', 'string', 'max:255'],
                'permanent_region' => ['required', 'string', 'max:255'],
                'permanent_barangay' => ['required', 'string', 'max:255'],
                'permanent_city' => ['required', 'string', 'max:255'],
                'permanent_province' => ['required', 'string', 'max:255'],
            ], [
                'permanent_house_no.required' => 'Permanent house number is required for non-permanent residents.',
                'permanent_street.required' => 'Permanent street is required for non-permanent residents.',
                'permanent_region.required' => 'Permanent region is required for non-permanent residents.',
                'permanent_barangay.required' => 'Permanent barangay is required for non-permanent residents.',
                'permanent_city.required' => 'Permanent city/municipality is required for non-permanent residents.',
                'permanent_province.required' => 'Permanent province is required for non-permanent residents.',
            ]);
        }

        // Auto-calculate age from birthdate
        $validated['age'] = Carbon::parse($validated['birthdate'])->age;

        // Normalize contact number to +63 format for consistency
        $contact = preg_replace('/[^0-9]/', '', $validated['contact_number']);
        if (str_starts_with($contact, '63') && strlen($contact) === 12) {
            $validated['contact_number'] = '+'.$contact;
        } elseif (str_starts_with($contact, '0') && strlen($contact) === 11) {
            $validated['contact_number'] = '+63'.substr($contact, 1);
        } elseif (strlen($contact) === 10) {
            $validated['contact_number'] = '+63'.$contact;
        }

        // Set the purok string for backwards compatibility (until old column is removed)
        $purok = Purok::find($validated['purok_id']);
        $validated['purok'] = $purok ? $purok->name : null;

        if (($validated['resident_type'] ?? null) === 'permanent') {
            $validated['permanent_house_no'] = null;
            $validated['permanent_street'] = null;
            $validated['permanent_region'] = null;
            $validated['permanent_barangay'] = null;
            $validated['permanent_city'] = null;
            $validated['permanent_province'] = null;
        }

        // Handle household linking
        $isHead = (bool) ($validated['is_head'] ?? false);
        unset($validated['is_head']);
        $hasHouseholdSelection = ! empty($validated['household_id']);
        $relationship = trim((string) ($validated['relationship_to_head'] ?? ''));

        if ($isHead && $hasHouseholdSelection) {
            throw ValidationException::withMessages([
                'household_id' => 'Head of household cannot be assigned as a member of another household.',
            ]);
        }

        if (! $isHead && $hasHouseholdSelection && $relationship === '') {
            throw ValidationException::withMessages([
                'relationship_to_head' => 'Relationship to head is required when assigning to an existing household.',
            ]);
        }

        if (! $isHead && $hasHouseholdSelection) {
            $household = Household::with('head')->find($validated['household_id']);
            if (! $household || ! $household->head || $household->head->role !== User::ROLE_RESIDENT) {
                throw ValidationException::withMessages([
                    'household_id' => 'Selected household does not have a valid resident head.',
                ]);
            }

            $allowedRelationships = $this->allowedHouseholdRelationshipsForAge((int) $validated['age']);
            if (! in_array($relationship, $allowedRelationships, true)) {
                throw ValidationException::withMessages([
                    'relationship_to_head' => 'Selected relationship is not valid for this resident age.',
                ]);
            }
        }

        if ($isHead) {
            // Ensure a household exists for this head
            $household = $user->householdAsHead;
            if (! $household) {
                $household = Household::create([
                    'head_id' => $user->id,
                    'purok' => $validated['purok'],
                ]);
            } else {
                $household->update(['purok' => $validated['purok']]);
            }

            $validated['household_id'] = $household->id;
            $validated['relationship_to_head'] = null;
        } elseif (! empty($validated['household_id'])) {
            // Member of an existing household
            $validated['relationship_to_head'] = $relationship;
        } else {
            // No household assigned
            $validated['household_id'] = null;
            $validated['relationship_to_head'] = null;
        }

        // Remove permission fields before mass-updating user attributes
        $permissionFields = ['can_manage_registrations', 'can_manage_blotter', 'can_manage_announcements', 'can_manage_complaints', 'can_manage_reports'];
        $permissionData = [];
        foreach ($permissionFields as $field) {
            if (array_key_exists($field, $validated)) {
                $permissionData[$field] = (bool) $validated[$field];
                unset($validated[$field]);
            }
        }

        $user->update($validated);

        if ($user->isHeadOfFamily() && $user->head_of_family_id === null) {
            $this->syncHeadAddressToLinkedMembers($user->fresh());
        }

        if ($user->role === User::ROLE_STAFF && ! empty($permissionData)) {
            $user->staffPermission()->updateOrCreate(
                ['user_id' => $user->id],
                $permissionData
            );

            AuditService::log('permissions_updated', $user, "Updated module permissions for {$user->full_name} via edit form");
        }

        return redirect()->route('admin.residents.show', $user)
            ->with('success', 'Resident record updated successfully.');
    }

    /**
     * Temporarily suspend a user account.
     */
    public function suspend(Request $request, User $user): RedirectResponse
    {
        // Prevent admin from suspending their own account
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'suspend' => 'You cannot suspend your own account.',
            ]);
        }

        // Never allow suspension of super admin accounts from UI.
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            return back()->withErrors([
                'suspend' => 'Super admin account cannot be suspended from this panel.',
            ]);
        }

        $user->forceFill([
            'is_suspended' => true,
            'suspended_at' => now(),
        ])->save();

        AuditService::log('account_suspended', $user, "Suspended account of {$user->full_name}");

        return back()->with('success', 'User account suspended successfully.');
    }

    /**
     * Remove suspension from a user account.
     */
    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        // Prevent admin from unsuspending their own account via this action (no-op safety)
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'suspend' => 'You cannot change your own suspension status.',
            ]);
        }

        // Super admin suspension is not managed in this panel.
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            return back()->withErrors([
                'suspend' => 'Super admin account status is managed via secure CLI.',
            ]);
        }

        $user->forceFill([
            'is_suspended' => false,
            'suspended_at' => null,
        ])->save();

        AuditService::log('account_unsuspended', $user, "Unsuspended account of {$user->full_name}");

        return back()->with('success', 'User account unsuspended successfully.');
    }

    /**
     * Admin: manually link a resident to a head of family.
     */
    public function linkFamily(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-household-link', $user);

        $before = $this->familyLinkSnapshot($user);
        $previousHeadId = $user->head_of_family_id;
        $previousHeadName = $user->headOfFamilyUser?->full_name;

        $validated = $request->validate([
            'head_of_family_id' => ['required', 'integer', 'exists:users,id'],
            'transfer_reason_code' => ['required', 'string', Rule::in(self::TRANSFER_REASON_CODES)],
            'transfer_reason_details' => ['nullable', 'string', 'max:255'],
        ]);

        if (($validated['transfer_reason_code'] ?? null) === 'other') {
            $request->validate([
                'transfer_reason_details' => ['required', 'string', 'min:5', 'max:255'],
            ]);
        }

        $head = User::findOrFail($validated['head_of_family_id']);

        // Validate the target is actually a head of family
        if ($head->head_of_family !== 'yes') {
            return back()->withErrors(['head_of_family_id' => 'The selected user is not a head of family.']);
        }

        // Cannot link to self
        if ($head->id === $user->id) {
            return back()->withErrors(['head_of_family_id' => 'A resident cannot be linked to themselves.']);
        }

        if ($previousHeadId !== null && $previousHeadId === $head->id) {
            return back()->withErrors(['head_of_family_id' => 'This resident is already linked to the selected head of family.']);
        }

        if ($head->head_of_family_id !== null) {
            return back()->withErrors(['head_of_family_id' => 'Selected head is already linked under another family and cannot be used as head.']);
        }

        if ($head->status !== User::STATUS_APPROVED || ($head->is_suspended ?? false)) {
            return back()->withErrors(['head_of_family_id' => 'The selected head of family is not active.']);
        }

        if (($head->age ?? 0) < 18) {
            return back()->withErrors(['head_of_family_id' => 'The selected head of family must be 18 years old or above.']);
        }

        if ($user->status !== User::STATUS_APPROVED || ($user->is_suspended ?? false)) {
            return back()->withErrors(['head_of_family_id' => 'Only active residents can be linked to a family.']);
        }

        if ($user->head_of_family === 'yes' && $user->head_of_family_id === null) {
            return back()->withErrors(['head_of_family_id' => 'A head of family account cannot be linked under another head.']);
        }

        if ($user->familyMembers()->exists() || $user->householdAsHead()->exists()) {
            return back()->withErrors(['head_of_family_id' => 'This resident currently has dependent family records and cannot be linked under another head.']);
        }

        if ($this->hasAnotherActiveHeadInHousehold($head)) {
            return back()->withErrors(['head_of_family_id' => 'This household already has another active head. Please resolve household integrity first.']);
        }

        $household = $this->resolveHeadHousehold($head);

        $user->update([
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'head_of_family' => 'no',
            'family_link_status' => 'linked',
            'house_no' => $head->house_no,
            'purok' => $head->purok,
            'purok_id' => $head->purok_id,
            'resident_type' => $head->resident_type,
        ]);

        $fromLabel = $previousHeadId ? ($previousHeadName ?: ('ID '.$previousHeadId)) : 'none';
        AuditService::log(
            'family_linked_admin',
            $user,
            "Admin linked {$user->full_name} to head {$head->full_name} (previous head: {$fromLabel}). Reason: {$this->buildTransferReasonText($validated)}. Snapshot: ".json_encode([
                'before' => $before,
                'after' => $this->familyLinkSnapshot($user->fresh()),
            ])
        );

        HouseholdHeadTransferLog::create([
            'resident_user_id' => $user->id,
            'old_head_user_id' => $previousHeadId,
            'new_head_user_id' => $head->id,
            'changed_by_user_id' => $request->user()->id,
            'action' => $previousHeadId ? HouseholdHeadTransferLog::ACTION_REASSIGN : HouseholdHeadTransferLog::ACTION_LINK,
            'reason_code' => $validated['transfer_reason_code'],
            'reason_details' => $validated['transfer_reason_details'] ?? null,
        ]);

        return redirect()->route('admin.residents.show', $user)
            ->with('success', "Resident linked to {$head->full_name} as head of family.");
    }

    /**
     * Admin: unlink a resident from their head of family.
     */
    public function unlinkFamily(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-household-link', $user);

        $before = $this->familyLinkSnapshot($user);
        $validated = $request->validate([
            'transfer_reason_code' => ['required', 'string', Rule::in(self::TRANSFER_REASON_CODES)],
            'transfer_reason_details' => ['nullable', 'string', 'max:255'],
        ]);

        if (($validated['transfer_reason_code'] ?? null) === 'other') {
            $request->validate([
                'transfer_reason_details' => ['required', 'string', 'min:5', 'max:255'],
            ]);
        }

        $previousHeadName = $user->headOfFamilyUser?->full_name;
        $previousHeadId = $user->head_of_family_id;

        if ($previousHeadId === null) {
            return back()->withErrors(['transfer_reason_code' => 'Resident is not linked to any head of family.']);
        }

        $user->update([
            'head_of_family_id' => null,
            'household_id' => null,
            'relationship_to_head' => null,
            'family_link_status' => 'unlinked',
        ]);

        $fromLabel = $previousHeadId ? ($previousHeadName ?: ('ID '.$previousHeadId)) : 'none';
        AuditService::log(
            'family_unlinked_admin',
            $user,
            "Admin unlinked {$user->full_name} from head {$fromLabel}. Reason: {$this->buildTransferReasonText($validated)}. Snapshot: ".json_encode([
                'before' => $before,
                'after' => $this->familyLinkSnapshot($user->fresh()),
            ])
        );

        HouseholdHeadTransferLog::create([
            'resident_user_id' => $user->id,
            'old_head_user_id' => $previousHeadId,
            'new_head_user_id' => null,
            'changed_by_user_id' => $request->user()->id,
            'action' => HouseholdHeadTransferLog::ACTION_UNLINK,
            'reason_code' => $validated['transfer_reason_code'],
            'reason_details' => $validated['transfer_reason_details'] ?? null,
        ]);

        return redirect()->route('admin.residents.show', $user)
            ->with('success', 'Family link removed successfully.');
    }

    /**
     * Admin/staff: directly transfer head role to linked household member.
     */
    public function transferHead(Request $request, User $user, User $member): RedirectResponse
    {
        $this->authorize('manage-household-link', $user);

        $validated = $request->validate([
            'direct_transfer_reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->headTransferService->directTransfer(
                $user,
                $member,
                $request->user(),
                $validated['direct_transfer_reason'] ?? null
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['transfer_head' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.residents.show', $user->fresh())
            ->with('success', 'Head of family transferred successfully.');
    }

    /**
     * Update module-level access permissions for a staff/admin user.
     */
    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN], true)) {
            return back()->withErrors(['permissions' => 'Permissions can only be set for staff or admin users.']);
        }

        $user->staffPermission()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'can_manage_registrations' => $request->boolean('can_manage_registrations'),
                'can_manage_blotter' => $request->boolean('can_manage_blotter'),
                'can_manage_announcements' => $request->boolean('can_manage_announcements'),
                'can_manage_complaints' => $request->boolean('can_manage_complaints'),
                'can_manage_reports' => $request->boolean('can_manage_reports'),
            ]
        );

        AuditService::log('permissions_updated', $user, "Updated module permissions for {$user->full_name}");

        return back()->with('success', 'Module permissions updated successfully.');
    }

    private function resolveHeadHousehold(User $head): Household
    {
        $household = $head->householdAsHead;

        if (! $household) {
            $household = Household::create([
                'head_id' => $head->id,
                'purok' => $head->purok ?? '',
            ]);
        } elseif (($head->purok ?? '') !== '' && $household->purok !== $head->purok) {
            $household->update(['purok' => $head->purok]);
        }

        return $household;
    }

    private function hasAnotherActiveHeadInHousehold(User $head): bool
    {
        $household = $head->householdAsHead;
        if (! $household) {
            return false;
        }

        return User::query()
            ->where('role', User::ROLE_RESIDENT)
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false)
            ->where('household_id', $household->id)
            ->where('head_of_family', 'yes')
            ->whereNull('head_of_family_id')
            ->where('id', '!=', $head->id)
            ->exists();
    }

    private function familyLinkSnapshot(User $user): array
    {
        return [
            'head_of_family_id' => $user->head_of_family_id,
            'household_id' => $user->household_id,
            'head_of_family' => $user->head_of_family,
            'family_link_status' => $user->family_link_status,
            'relationship_to_head' => $user->relationship_to_head,
            'household_connection_type' => $user->household_connection_type,
            'connection_note' => $user->connection_note,
            'resident_type' => $user->resident_type,
        ];
    }

    private function buildTransferReasonText(array $validated): string
    {
        $label = HouseholdHeadTransferLog::REASON_LABELS[$validated['transfer_reason_code']] ?? $validated['transfer_reason_code'];
        $details = trim((string) ($validated['transfer_reason_details'] ?? ''));

        return $details !== '' ? "{$label} - {$details}" : $label;
    }

    private function syncHeadAddressToLinkedMembers(User $head): void
    {
        $household = $this->resolveHeadHousehold($head);

        User::query()
            ->where('head_of_family_id', $head->id)
            ->update([
                'household_id' => $household->id,
                'house_no' => $head->house_no,
                'purok' => $head->purok,
                'purok_id' => $head->purok_id,
                'resident_type' => $head->resident_type,
                'updated_at' => now(),
            ]);

        FamilyMember::query()
            ->where('head_user_id', $head->id)
            ->update([
                'household_id' => $household->id,
                'house_no' => $head->house_no,
                'purok' => $head->purok,
                'purok_id' => $head->purok_id,
                'resident_type' => $head->resident_type,
                'updated_at' => now(),
            ]);

        AuditService::log(
            'head_profile_cascade_sync',
            $head,
            "Cascaded head address updates from {$head->full_name} to linked residents and family members."
        );
    }

    private function allowedHouseholdRelationshipsForAge(int $age): array
    {
        if ($age < 18) {
            return [
                'son',
                'daughter',
                'brother',
                'sister',
                'nephew',
                'niece',
                'cousin',
                'other',
            ];
        }

        return self::HOUSEHOLD_RELATIONSHIP_OPTIONS;
    }

    /**
     * Update the official position for a user.
     */
    public function updatePosition(Request $request, User $user): RedirectResponse
    {
        if ($user->role === User::ROLE_SUPER_ADMIN) {
            abort(404);
        }

        if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN], true)) {
            return back()->withErrors([
                'position_id' => 'Only admin/staff accounts can hold official positions.',
            ]);
        }

        $validated = $request->validate([
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'position_reason_code' => ['nullable', 'string', Rule::in(self::POSITION_REASON_CODES)],
            'position_reason_details' => ['nullable', 'string', 'max:255'],
        ]);

        $newPositionId = $validated['position_id'] ?? null;

        $oldPositionId = $user->position_id;
        if ((int) ($oldPositionId ?? 0) === (int) ($newPositionId ?? 0)) {
            return back()->with('success', 'Position remains unchanged.');
        }

        $reasonCode = $validated['position_reason_code'] ?? null;
        $reasonDetails = trim((string) ($validated['position_reason_details'] ?? ''));

        if (! $reasonCode) {
            return back()->withErrors([
                'position_reason_code' => 'Please select a reason for changing this position.',
            ])->withInput();
        }
        if ($reasonCode === 'other' && mb_strlen($reasonDetails) < 5) {
            return back()->withErrors([
                'position_reason_details' => 'Please provide at least 5 characters for Other reason.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($user, $newPositionId, $oldPositionId, $reasonCode, $reasonDetails): void {
                $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

                $newPosition = null;
                if ($newPositionId) {
                    $newPosition = Position::query()->whereKey($newPositionId)->lockForUpdate()->firstOrFail();

                    // Lock current holders of this position before counting to reduce race conditions.
                    $holderIds = User::query()
                        ->where('position_id', $newPosition->id)
                        ->whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
                        ->lockForUpdate()
                        ->pluck('id');

                    $holderCount = $holderIds
                        ->filter(fn ($id) => (int) $id !== (int) $lockedUser->id)
                        ->count();

                    if ($holderCount >= (int) $newPosition->max_seats) {
                        throw ValidationException::withMessages([
                            'position_id' => 'Maximum number of '.$newPosition->name.' ('.$newPosition->max_seats.') reached.',
                        ]);
                    }
                }

                $oldPositionName = $lockedUser->position?->name ?? $lockedUser->position_title ?? 'none';
                $newPositionName = $newPosition?->name ?? 'none';

                $lockedUser->forceFill([
                    'position_id' => $newPosition?->id,
                    'position_title' => $newPositionName !== 'none' ? $newPositionName : null,
                ])->save();

                PositionChangeLog::create([
                    'resident_user_id' => $lockedUser->id,
                    'old_position_id' => $oldPositionId,
                    'new_position_id' => $newPosition?->id,
                    'changed_by_user_id' => (int) auth()->id(),
                    'reason_code' => $reasonCode,
                    'reason_details' => $reasonDetails !== '' ? $reasonDetails : null,
                ]);

                AuditService::log(
                    'position_updated',
                    $lockedUser,
                    "Changed position from {$oldPositionName} to {$newPositionName}. Reason: {$this->buildPositionReasonText($reasonCode, $reasonDetails)}"
                );
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return back()->with('success', 'Position updated successfully.');
    }

    private function buildPositionReasonText(string $reasonCode, string $reasonDetails = ''): string
    {
        $label = PositionChangeLog::REASON_LABELS[$reasonCode] ?? $reasonCode;
        $details = trim($reasonDetails);

        return $details !== '' ? "{$label} - {$details}" : $label;
    }

    /**
     * Display probable duplicate resident groups for admin review.
     */
    public function duplicateResolver()
    {
        $groups = User::query()
            ->selectRaw('LOWER(TRIM(first_name)) as first_name_key')
            ->selectRaw('LOWER(TRIM(COALESCE(middle_name, ""))) as middle_name_key')
            ->selectRaw('LOWER(TRIM(last_name)) as last_name_key')
            ->selectRaw('TRIM(COALESCE(suffix, "")) as suffix_key')
            ->selectRaw('DATE(birthdate) as birthdate_key')
            ->selectRaw('COUNT(*) as duplicate_count')
            ->where('role', User::ROLE_RESIDENT)
            ->whereNotNull('birthdate')
            ->groupBy([
                DB::raw('LOWER(TRIM(first_name))'),
                DB::raw('LOWER(TRIM(COALESCE(middle_name, "")))'),
                DB::raw('LOWER(TRIM(last_name))'),
                DB::raw('TRIM(COALESCE(suffix, ""))'),
                DB::raw('DATE(birthdate)'),
            ])
            ->having('duplicate_count', '>', 1)
            ->orderByDesc('duplicate_count')
            ->limit(50)
            ->get()
            ->map(function ($group) {
                $members = User::query()
                    ->where('role', User::ROLE_RESIDENT)
                    ->whereDate('birthdate', $group->birthdate_key)
                    ->whereRaw('LOWER(TRIM(first_name)) = ?', [$group->first_name_key])
                    ->whereRaw('LOWER(TRIM(COALESCE(middle_name, ""))) = ?', [$group->middle_name_key])
                    ->whereRaw('LOWER(TRIM(last_name)) = ?', [$group->last_name_key])
                    ->whereRaw('TRIM(COALESCE(suffix, "")) = ?', [$group->suffix_key])
                    ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
                    ->orderBy('id')
                    ->get();

                $members->each(function (User $member): void {
                    $transfer = $this->transferCountsForResident($member->id);
                    $member->setAttribute('transfer_total', $transfer['total']);
                    $member->setAttribute('transfer_breakdown', $transfer['breakdown']);
                });

                $displayName = trim(implode(' ', array_filter([
                    ucfirst((string) $group->first_name_key),
                    ucfirst((string) $group->middle_name_key),
                    ucfirst((string) $group->last_name_key),
                    (string) $group->suffix_key,
                ])));

                return [
                    'display_name' => $displayName,
                    'birthdate' => $group->birthdate_key,
                    'duplicate_count' => (int) $group->duplicate_count,
                    'members' => $members,
                ];
            });

        $recentMerges = ResidentMergeLog::with(['primary', 'secondary', 'performedBy'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.residents.duplicates', compact('groups', 'recentMerges'));
    }

    /**
     * Merge a duplicate resident account into a selected primary resident account.
     */
    public function mergeDuplicateResident(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'primary_user_id' => ['required', 'integer', 'exists:users,id'],
            'secondary_user_id' => ['required', 'integer', 'exists:users,id', 'different:primary_user_id'],
            'confirm_same_person' => ['accepted'],
        ]);

        $primary = User::findOrFail($validated['primary_user_id']);
        $secondary = User::findOrFail($validated['secondary_user_id']);

        if ($primary->role !== User::ROLE_RESIDENT || $secondary->role !== User::ROLE_RESIDENT) {
            return back()->withErrors([
                'primary_user_id' => 'Duplicate merge is only available for resident accounts.',
            ]);
        }

        if ($secondary->familyMembers()->exists() || $secondary->householdAsHead()->exists()) {
            return back()->withErrors([
                'secondary_user_id' => 'Cannot merge this duplicate because it currently acts as a family head with dependents.',
            ]);
        }

        if ($this->residentIdentity($primary) !== $this->residentIdentity($secondary)) {
            return back()->withErrors([
                'primary_user_id' => 'Merge blocked: selected records do not have an exact identity match (name/suffix/birthdate).',
            ]);
        }

        DB::transaction(function () use ($primary, $secondary, $request): void {
            $transferredCounts = [];
            $tablesPayload = [];

            foreach ($this->duplicateMergeTables() as $table) {
                $ids = DB::table($table['name'])
                    ->where($table['column'], $secondary->id)
                    ->pluck('id')
                    ->all();

                $tablesPayload[$table['name']] = [
                    'column' => $table['column'],
                    'ids' => $ids,
                ];

                $updated = 0;
                if (! empty($ids)) {
                    $updated = DB::table($table['name'])
                        ->whereIn('id', $ids)
                        ->update([$table['column'] => $primary->id]);
                }

                $transferredCounts[$table['label']] = $updated;
            }

            $primaryFamilySnapshot = [
                'head_of_family_id' => $primary->head_of_family_id,
                'household_id' => $primary->household_id,
                'family_link_status' => $primary->family_link_status,
                'relationship_to_head' => $primary->relationship_to_head,
            ];

            $secondarySnapshot = [
                'head_of_family_id' => $secondary->head_of_family_id,
                'household_id' => $secondary->household_id,
                'relationship_to_head' => $secondary->relationship_to_head,
                'family_link_status' => $secondary->family_link_status,
                'status' => $secondary->status,
                'is_suspended' => $secondary->is_suspended,
                'suspended_at' => $secondary->suspended_at,
            ];

            // Preserve an existing family link from duplicate if primary is currently unlinked.
            if ($primary->head_of_family_id === null && $secondary->head_of_family_id !== null) {
                $primary->update([
                    'head_of_family_id' => $secondary->head_of_family_id,
                    'household_id' => $secondary->household_id,
                    'family_link_status' => $secondary->family_link_status ?: 'linked',
                    'relationship_to_head' => $secondary->relationship_to_head,
                ]);
            }

            $secondary->update([
                'head_of_family_id' => null,
                'household_id' => null,
                'relationship_to_head' => null,
                'family_link_status' => 'unlinked',
                'status' => User::STATUS_REJECTED,
                'is_suspended' => true,
                'suspended_at' => now(),
            ]);

            $summary = collect($transferredCounts)
                ->filter(fn ($count) => $count > 0)
                ->map(fn ($count, $table) => "{$table}:{$count}")
                ->implode(', ');
            $summary = $summary !== '' ? $summary : 'no linked records transferred';

            AuditService::log(
                'resident_duplicate_merged_primary',
                $primary,
                "Primary resident {$primary->full_name} absorbed duplicate {$secondary->full_name}; {$summary}."
            );

            AuditService::log(
                'resident_duplicate_merged_secondary',
                $secondary,
                "Duplicate resident {$secondary->full_name} merged into {$primary->full_name}; account suspended."
            );

            ResidentMergeLog::create([
                'primary_user_id' => $primary->id,
                'secondary_user_id' => $secondary->id,
                'performed_by' => optional($request->user())->id,
                'tables_payload' => $tablesPayload,
                'primary_snapshot' => $primaryFamilySnapshot,
                'secondary_snapshot' => $secondarySnapshot,
            ]);
        });

        return redirect()->route('admin.residents.duplicates')
            ->with('success', "Duplicate resident merged successfully. {$secondary->full_name} is now suspended.");
    }

    /**
     * Undo a previous duplicate merge operation using the merge log.
     */
    public function undoDuplicateMerge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'merge_log_id' => ['required', 'integer', 'exists:resident_merge_logs,id'],
        ]);

        $log = ResidentMergeLog::with(['primary', 'secondary'])->findOrFail($validated['merge_log_id']);

        if ($log->undone_at !== null) {
            return back()->withErrors([
                'undo' => 'This merge has already been undone.',
            ]);
        }

        $primary = $log->primary;
        $secondary = $log->secondary;

        if (! $primary || ! $secondary) {
            return back()->withErrors([
                'undo' => 'Unable to undo merge because one of the accounts no longer exists.',
            ]);
        }

        if ($secondary->status !== User::STATUS_REJECTED || ! $secondary->is_suspended) {
            return back()->withErrors([
                'undo' => 'Undo is only allowed while the merged duplicate remains in suspended/rejected state.',
            ]);
        }

        DB::transaction(function () use ($log, $primary, $secondary): void {
            $tablesPayload = $log->tables_payload ?? [];

            foreach ($tablesPayload as $tableName => $info) {
                $column = $info['column'] ?? 'user_id';
                $ids = $info['ids'] ?? [];

                if (empty($ids)) {
                    continue;
                }

                DB::table($tableName)
                    ->whereIn('id', $ids)
                    ->where($column, $primary->id)
                    ->update([$column => $secondary->id]);
            }

            $primarySnapshot = $log->primary_snapshot ?? [];
            $secondarySnapshot = $log->secondary_snapshot ?? [];

            $primary->update([
                'head_of_family_id' => $primarySnapshot['head_of_family_id'] ?? $primary->head_of_family_id,
                'household_id' => $primarySnapshot['household_id'] ?? $primary->household_id,
                'family_link_status' => $primarySnapshot['family_link_status'] ?? $primary->family_link_status,
                'relationship_to_head' => $primarySnapshot['relationship_to_head'] ?? $primary->relationship_to_head,
            ]);

            $secondary->update([
                'head_of_family_id' => $secondarySnapshot['head_of_family_id'] ?? $secondary->head_of_family_id,
                'household_id' => $secondarySnapshot['household_id'] ?? $secondary->household_id,
                'relationship_to_head' => $secondarySnapshot['relationship_to_head'] ?? $secondary->relationship_to_head,
                'family_link_status' => $secondarySnapshot['family_link_status'] ?? $secondary->family_link_status,
                'status' => $secondarySnapshot['status'] ?? $secondary->status,
                'is_suspended' => $secondarySnapshot['is_suspended'] ?? $secondary->is_suspended,
                'suspended_at' => $secondarySnapshot['suspended_at'] ?? $secondary->suspended_at,
            ]);

            $log->update([
                'undone_at' => now(),
            ]);

            AuditService::log(
                'resident_duplicate_merge_undone_primary',
                $primary,
                "Undo merge: restored records previously transferred from {$secondary->full_name}."
            );

            AuditService::log(
                'resident_duplicate_merge_undone_secondary',
                $secondary,
                "Undo merge: restored duplicate resident after merge into {$primary->full_name}."
            );
        });

        return redirect()->route('admin.residents.duplicates')
            ->with('success', 'Duplicate merge has been undone successfully.');
    }
}
