<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdHeadTransferRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    public const REQUESTED_BY_ROLE_HEAD = 'head';
    public const REQUESTED_BY_ROLE_MEMBER = 'member';

    public const REASON_LABELS = [
        'relocation' => 'Relocation',
        'incapacity' => 'Incapacity',
        'voluntary' => 'Voluntary',
        'other' => 'Other',
    ];

    protected $fillable = [
        'household_id',
        'current_head_id',
        'new_head_id',
        'requested_by',
        'requested_by_role',
        'reason',
        'details',
        'status',
        'review_note',
        'processed_by',
        'processed_at',
        'processed_transfer_log_id',
        'pending_household_lock',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $request): void {
            if (empty($request->requested_by) && ! empty($request->requester_user_id)) {
                $request->requested_by = (int) $request->requester_user_id;
            }
            if (empty($request->current_head_id) && ! empty($request->current_head_user_id)) {
                $request->current_head_id = (int) $request->current_head_user_id;
            }
            if (empty($request->new_head_id) && ! empty($request->requested_head_user_id)) {
                $request->new_head_id = (int) $request->requested_head_user_id;
            }
            if (empty($request->reason) && ! empty($request->reason_code)) {
                $request->reason = (string) $request->reason_code;
            }
            if (empty($request->details) && ! empty($request->reason_details)) {
                $request->details = (string) $request->reason_details;
            }
            if (empty($request->review_note) && ! empty($request->review_decision_notes)) {
                $request->review_note = (string) $request->review_decision_notes;
            }
            if (empty($request->processed_by) && ! empty($request->reviewed_by_user_id)) {
                $request->processed_by = (int) $request->reviewed_by_user_id;
            }
            if (empty($request->processed_at) && ! empty($request->reviewed_at)) {
                $request->processed_at = $request->reviewed_at;
            }

            $request->requester_user_id = $request->requested_by;
            $request->current_head_user_id = $request->current_head_id;
            $request->requested_head_user_id = $request->new_head_id;
            $request->reason_code = $request->reason;
            $request->reason_details = $request->details;
            $request->review_decision_notes = $request->review_note;
            $request->reviewed_by_user_id = $request->processed_by;
            $request->reviewed_at = $request->processed_at;

            $request->pending_household_lock = (
                $request->status === self::STATUS_PENDING
                && ! empty($request->household_id)
            ) ? (int) $request->household_id : null;
        });
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function currentHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_head_id');
    }

    public function newHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_head_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function processedTransferLog(): BelongsTo
    {
        return $this->belongsTo(HouseholdHeadTransferLog::class, 'processed_transfer_log_id');
    }

    public function requestedHead(): BelongsTo
    {
        return $this->newHead();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->processedBy();
    }

    public function reasonLabel(): string
    {
        return self::REASON_LABELS[$this->reason] ?? $this->reason;
    }
}
