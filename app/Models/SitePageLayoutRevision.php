<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageLayoutRevision extends Model
{
    protected $fillable = [
        'page_key',
        'sections',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
