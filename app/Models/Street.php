<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Street extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to get only active streets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Puroks where this street is valid.
     */
    public function puroks(): BelongsToMany
    {
        return $this->belongsToMany(Purok::class, 'purok_street')
            ->withTimestamps()
            ->orderBy('name');
    }
}
