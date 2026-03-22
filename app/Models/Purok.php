<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Purok extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to get only active puroks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Residents belonging to this purok.
     */
    public function residents()
    {
        return $this->hasMany(User::class, 'purok_id');
    }

    /**
     * Streets associated with this purok.
     */
    public function streets(): BelongsToMany
    {
        return $this->belongsToMany(Street::class, 'purok_street')
            ->withTimestamps()
            ->orderBy('name');
    }
}
