<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlotterRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_RELEASED = 'released';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_RELEASED,
    ];

    public const REJECTION_REASON_INCOMPLETE_INFO = 'incomplete_information';

    public const REJECTION_REASON_INSUFFICIENT_DETAILS = 'insufficient_case_details';

    public const REJECTION_REASON_INVALID_RECORD = 'invalid_or_not_owned_record';

    public const REJECTION_REASON_DUPLICATE_REQUEST = 'duplicate_or_active_request';

    public const REJECTION_REASON_OUTSIDE_JURISDICTION = 'outside_barangay_jurisdiction';

    public const REJECTION_REASON_OTHERS = 'others';

    public const REJECTION_REASON_LABELS = [
        self::REJECTION_REASON_INCOMPLETE_INFO => 'Incomplete information submitted',
        self::REJECTION_REASON_INSUFFICIENT_DETAILS => 'Insufficient case details for processing',
        self::REJECTION_REASON_INVALID_RECORD => 'Invalid or unauthorized blotter record selected',
        self::REJECTION_REASON_DUPLICATE_REQUEST => 'Duplicate request while another request is active',
        self::REJECTION_REASON_OUTSIDE_JURISDICTION => 'Request is outside barangay processing jurisdiction',
        self::REJECTION_REASON_OTHERS => 'Others',
    ];

    /**
     * Allowed status transitions.
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED => [self::STATUS_RELEASED],
        self::STATUS_REJECTED => [],
        self::STATUS_RELEASED => [],
    ];

    /**
     * Only content fields are mass-assignable.
     *
     * Guarded (set via forceFill only):
     *   user_id, status, processed_by, processed_at
     */
    protected $fillable = [
        'blotter_id',
        'purpose',
        'rejection_reason_code',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    // ── Helpers ──

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_RELEASED,
        ], true);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::TRANSITIONS[$this->status] ?? [];

        return in_array($newStatus, $allowed, true);
    }

    public function isTerminal(): bool
    {
        return empty(self::TRANSITIONS[$this->status] ?? []);
    }

    public static function rejectionReasonOptions(): array
    {
        return self::REJECTION_REASON_LABELS;
    }

    public static function rejectionReasonCodes(): array
    {
        return array_keys(self::REJECTION_REASON_LABELS);
    }

    public function rejectionReasonLabel(): ?string
    {
        if (empty($this->rejection_reason_code)) {
            return null;
        }

        return self::REJECTION_REASON_LABELS[$this->rejection_reason_code] ?? null;
    }

    // ── Relationships ──

    public function blotter(): BelongsTo
    {
        return $this->belongsTo(Blotter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
