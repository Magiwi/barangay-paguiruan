<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentMergeLog extends Model
{
    protected $fillable = [
        'primary_user_id',
        'secondary_user_id',
        'performed_by',
        'tables_payload',
        'primary_snapshot',
        'secondary_snapshot',
        'undone_at',
    ];

    protected $casts = [
        'tables_payload' => 'array',
        'primary_snapshot' => 'array',
        'secondary_snapshot' => 'array',
        'undone_at' => 'datetime',
    ];

    public function primary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_user_id');
    }

    public function secondary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secondary_user_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}

