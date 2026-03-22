<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdHeadTransferLog extends Model
{
    public const ACTION_LINK = 'link';
    public const ACTION_REASSIGN = 'reassign';
    public const ACTION_UNLINK = 'unlink';

    public const REASON_LABELS = [
        'relocation' => 'Relocation',
        'incapacity' => 'Incapacity',
        'voluntary' => 'Voluntary',
        'correction_error' => 'Correction of data entry error',
        'residence_update' => 'Residence or household update',
        'head_unavailable' => 'Previous head is unavailable',
        'duplicate_cleanup' => 'Duplicate cleanup adjustment',
        'other' => 'Other',
    ];

    protected $fillable = [
        'resident_user_id',
        'old_head_user_id',
        'new_head_user_id',
        'changed_by_user_id',
        'action',
        'reason_code',
        'reason_details',
    ];

    public function residentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_user_id');
    }

    public function oldHeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_head_user_id');
    }

    public function newHeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_head_user_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function reasonLabel(): string
    {
        return self::REASON_LABELS[$this->reason_code] ?? $this->reason_code;
    }
}
