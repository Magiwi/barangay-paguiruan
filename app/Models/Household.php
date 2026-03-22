<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    protected $fillable = [
        'head_id',
        'purok',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'household_id');
    }

    /**
     * Non-account family member records for this household.
     */
    public function memberRecords(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'household_id');
    }
}
