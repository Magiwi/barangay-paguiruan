<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HearingReschedule extends Model
{
    protected $fillable = [
        'hearing_id',
        'old_hearing_date',
        'old_hearing_time',
        'new_hearing_date',
        'new_hearing_time',
        'reason',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'old_hearing_date' => 'date',
            'new_hearing_date' => 'date',
        ];
    }

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(Hearing::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
