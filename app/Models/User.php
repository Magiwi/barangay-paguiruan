<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** Supported roles. Use for gates, middleware, or role management later. */
    public const ROLE_RESIDENT = 'resident';

    public const ROLE_STAFF = 'staff';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * The attributes that are mass assignable.
     *
     * Note: role, status, is_suspended, and suspended_at are intentionally
     * excluded to prevent mass-assignment privilege escalation. Always set
     * these explicitly via forceFill() or dedicated setter methods.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'house_no',
        'purok',
        'purok_id',
        'street_name',
        'sitio_subdivision',
        'contact_number',
        'age',
        'gender',
        'birthdate',
        'civil_status',
        'head_of_family',
        'head_of_family_id',
        'family_link_status',
        'head_first_name',
        'head_middle_name',
        'head_last_name',
        'resident_type',
        'household_id',
        'relationship_to_head',
        'household_connection_type',
        'connection_note',
        'permanent_house_no',
        'permanent_street',
        'permanent_region',
        'permanent_barangay',
        'permanent_city',
        'permanent_province',
        'email',
        'password',
        // Resident classification
        'is_pwd',
        'is_senior',
        'pwd_status',
        'senior_status',
        'pwd_proof_path',
        'senior_proof_path',
        'government_id_type',
        'government_id_path',
        'rejection_reason_code',
        'rejection_reason_details',
    ];

    /** Registration approval status. */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    public const HEAD_YES = 'yes';

    public const REGISTRATION_REJECTION_REASON_LABELS = [
        'underage' => 'Underage applicant',
        'invalid_government_id' => 'Invalid government ID',
        'incomplete_profile' => 'Incomplete profile information',
        'duplicate_registration' => 'Possible duplicate registration',
        'address_unverifiable' => 'Address cannot be verified',
        'other' => 'Other',
    ];

    public const HOUSEHOLD_CONNECTION_TYPE_LABELS = [
        'family_member' => 'Family Member',
        'boarder' => 'Boarder',
        'helper' => 'Helper',
        'guardian_dependent' => 'Guardian/Dependent',
        'other' => 'Other',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthdate' => 'date',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
            'is_pwd' => 'boolean',
            'is_senior' => 'boolean',
        ];
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Users that should be included in system counts.
     */
    public function scopeCountable($query)
    {
        return $query->where('role', '!=', self::ROLE_SUPER_ADMIN);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->userNotifications()->where('is_read', false)->count();
    }

    public function certificateRequests()
    {
        return $this->hasMany(CertificateRequest::class);
    }

    public function blotterRequests()
    {
        return $this->hasMany(BlotterRequest::class);
    }

    public function issueReports()
    {
        return $this->hasMany(IssueReport::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    /**
     * The purok this user belongs to.
     */
    public function purokRelation()
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    public function householdAsHead()
    {
        return $this->hasOne(Household::class, 'head_id');
    }

    /**
     * Approval log entries for this user (when this user was approved/rejected).
     */
    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class, 'user_id');
    }

    /**
     * Approval log entries performed by this user (when this user approved/rejected others).
     */
    public function performedApprovals()
    {
        return $this->hasMany(ApprovalLog::class, 'performed_by');
    }

    /**
     * Permit applications submitted by this user.
     */
    public function permits()
    {
        return $this->hasMany(Permit::class);
    }

    /**
     * Staff-level module permissions for this user.
     */
    public function staffPermission()
    {
        return $this->hasOne(StaffPermission::class);
    }

    /**
     * The official position assigned to this user.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Official appointment records for this user.
     */
    public function officials()
    {
        return $this->hasMany(Official::class);
    }

    /**
     * The current active official record (if any).
     */
    public function activeOfficial()
    {
        return $this->hasOne(Official::class)->where('is_active', true)->latest();
    }

    public function registrationRejectionReasonLabel(): ?string
    {
        if (! $this->rejection_reason_code) {
            return null;
        }

        return self::REGISTRATION_REJECTION_REASON_LABELS[$this->rejection_reason_code] ?? $this->rejection_reason_code;
    }

    public function householdConnectionTypeLabel(): ?string
    {
        if (! $this->household_connection_type) {
            return null;
        }

        return self::HOUSEHOLD_CONNECTION_TYPE_LABELS[$this->household_connection_type] ?? $this->household_connection_type;
    }

    // ── Family / Household relationships ──
    /**
     * The head of family this resident belongs to.
     */
    public function headOfFamilyUser()
    {
        return $this->belongsTo(User::class, 'head_of_family_id');
    }

    /**
     * Family members where this user is the head.
     */
    public function familyMembers()
    {
        return $this->hasMany(User::class, 'head_of_family_id');
    }

    /**
     * Non-account family member records linked to this head.
     */
    public function familyMemberRecords()
    {
        return $this->hasMany(FamilyMember::class, 'head_user_id');
    }


    /**
     * Check if this user is a head of family.
     */
    public function isHeadOfFamily(): bool
    {
        return $this->head_of_family === self::HEAD_YES;
    }

    /**
     * Can manage own family as a valid household head.
     */
    public function canManageOwnFamily(): bool
    {
        return in_array($this->role, [
            self::ROLE_RESIDENT,
            self::ROLE_ADMIN,
            self::ROLE_STAFF,
        ], true)
            && $this->status === self::STATUS_APPROVED
            && ! (bool) ($this->is_suspended ?? false)
            && $this->head_of_family === self::HEAD_YES
            && $this->head_of_family_id === null;
    }

    /**
     * Can manage family records for any household (admin/staff only).
     */
    public function canManageAnyFamily(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_STAFF,
        ], true)
            && $this->status === self::STATUS_APPROVED
            && ! (bool) ($this->is_suspended ?? false);
    }

    /**
     * Get full name helper.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Display name with position title when applicable.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;

        $positionName = $this->position?->name ?? $this->position_title;

        if ($positionName) {
            return $name . ' (' . $positionName . ')';
        }

        return $name;
    }

    /**
     * Check if this user has access to a specific module.
     * Admins always have full access.
     */
    public function hasModuleAccess(string $module): bool
    {
        if (in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true)) {
            return true;
        }

        $permission = $this->staffPermission;

        if (! $permission) {
            return false;
        }

        return match ($module) {
            'registrations' => $permission->can_manage_registrations,
            'blotter' => $permission->can_manage_blotter,
            'announcements' => $permission->can_manage_announcements,
            'complaints' => $permission->can_manage_complaints,
            'reports' => $permission->can_manage_reports,
            default => false,
        };
    }

    /**
     * Alias for hasModuleAccess for cleaner Blade usage.
     */
    public function canAccess(string $module): bool
    {
        return $this->hasModuleAccess($module);
    }

    /**
     * Check if this staff/admin user has ANY module assigned.
     */
    public function hasAnyModuleAccess(): bool
    {
        if (in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true)) {
            return true;
        }

        $p = $this->staffPermission;

        if (! $p) {
            return false;
        }

        return $p->can_manage_registrations
            || $p->can_manage_blotter
            || $p->can_manage_announcements
            || $p->can_manage_complaints
            || $p->can_manage_reports;
    }
}
