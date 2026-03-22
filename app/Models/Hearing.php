<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hearing extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_DONE = 'done';
    public const STATUS_NO_SHOW = 'no_show';

    public const ATTENDANCE_PRESENT = 'present';
    public const ATTENDANCE_ABSENT = 'absent';

    public const RESULT_SETTLED = 'settled';
    public const RESULT_NOT_SETTLED = 'not_settled';
    public const RESULT_RESCHEDULE = 'reschedule';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_ONGOING,
        self::STATUS_DONE,
        self::STATUS_NO_SHOW,
    ];

    public const ATTENDANCE_OPTIONS = [
        self::ATTENDANCE_PRESENT,
        self::ATTENDANCE_ABSENT,
    ];

    public const RESULTS = [
        self::RESULT_SETTLED,
        self::RESULT_NOT_SETTLED,
        self::RESULT_RESCHEDULE,
    ];

    protected $fillable = [
        'blotter_id',
        'summon_id',
        'hearing_date',
        'hearing_time',
        'lupon_assigned',
        'complainant_attendance',
        'respondent_attendance',
        'status',
        'result',
        'notes',
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

    public function summon(): BelongsTo
    {
        return $this->belongsTo(Summon::class);
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(HearingReschedule::class);
    }
}
