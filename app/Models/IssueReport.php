<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueReport extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    /**
     * Statuses that require a resolution summary (remarks).
     */
    public const REQUIRES_REMARKS = [
        self::STATUS_CLOSED,
    ];

    /**
     * Allowed forward-only transitions.
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => self::STATUS_IN_PROGRESS,
        self::STATUS_IN_PROGRESS => self::STATUS_RESOLVED,
        self::STATUS_RESOLVED => self::STATUS_CLOSED,
        // closed → nothing
    ];

    /**
     * Only content fields are mass-assignable.
     *
     * Guarded (set via forceFill only):
     *   user_id, status, assigned_to, closed_at, resolved_at, resolved_by
     */
    protected $fillable = [
        'subject',
        'category',
        'description',
        'attachment_path',
        'location',
        'purok_id',
        'remarks',
        'resolution_notes',
        'action_taken',
        'after_photo_path',
        'other_details',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Categories that require after_photo when resolving.
     */
    public const INFRAS_REQUIRE_PHOTO = [
        'Infrastructure',
        'Flooding',
        'Sanitation',
    ];

    /**
     * Categories that require other_details when resolving.
     */
    public const OTHER_REQUIRES_DETAILS = ['Other'];

    /**
     * Action taken options by category group (for validation / UI).
     */
    public const ACTION_TAKEN_OPTIONS = [
        'infra' => ['repaired', 'cleaned', 'replaced'],
        'noise' => ['warning', 'mediation'],
        'safety' => ['reported', 'patrol increased'],
    ];

    // ── Helpers ──

    public function canTransitionTo(string $newStatus): bool
    {
        return isset(self::TRANSITIONS[$this->status])
            && self::TRANSITIONS[$this->status] === $newStatus;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Whether the given status requires a resolution summary.
     */
    public static function requiresRemarks(string $status): bool
    {
        return in_array($status, self::REQUIRES_REMARKS, true);
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Whether this category requires after_photo when resolving.
     */
    public function requiresAfterPhoto(): bool
    {
        return in_array($this->category, self::INFRAS_REQUIRE_PHOTO, true);
    }

    /**
     * Whether this category requires other_details when resolving.
     */
    public function requiresOtherDetails(): bool
    {
        return in_array($this->category, self::OTHER_REQUIRES_DETAILS, true);
    }

    /**
     * Days since the complaint was created.
     */
    public function daysOpen(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ComplaintNote::class)->latest();
    }
}
