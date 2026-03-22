<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Summon extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SERVED = 'served';
    public const STATUS_NO_SHOW = 'no_show';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SERVED,
        self::STATUS_NO_SHOW,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'blotter_id',
        'summon_number',
        'hearing_date',
        'hearing_time',
        'lupon_assigned',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'hearing_date' => 'date',
        ];
    }

    public function blotter(): BelongsTo
    {
        return $this->belongsTo(Blotter::class);
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(Hearing::class);
    }
}
