<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionChangeLog extends Model
{
    public const REASON_LABELS = [
        'organizational_update' => 'Organizational update',
        'seat_reallocation' => 'Seat reallocation',
        'role_reassignment' => 'Role reassignment',
        'compliance_adjustment' => 'Compliance adjustment',
        'other' => 'Other',
    ];

    protected $fillable = [
        'resident_user_id',
        'old_position_id',
        'new_position_id',
        'changed_by_user_id',
        'reason_code',
        'reason_details',
    ];

    public function residentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_user_id');
    }

    public function oldPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'old_position_id');
    }

    public function newPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'new_position_id');
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
