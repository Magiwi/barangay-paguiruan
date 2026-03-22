<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permit extends Model
{
    protected $fillable = [
        'user_id',
        'permit_type',
        'purpose',
        'extra_fields',
        'document_path',
        'status',
        'remarks',
        'released_at',
        'released_by',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
            'extra_fields' => 'array',
        ];
    }

    /**
     * The resident who applied for this permit.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The admin/staff who released this permit.
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
