<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlotterRevision extends Model
{
    protected $fillable = [
        'blotter_id',
        'changed_by',
        'action',
        'change_note',
        'changed_fields',
        'before_data',
        'after_data',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'before_data' => 'array',
            'after_data' => 'array',
        ];
    }

    public function blotter(): BelongsTo
    {
        return $this->belongsTo(Blotter::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

