<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'head_user_id',
        'household_id',
        'linked_user_id',
        'purok_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthdate',
        'age',
        'gender',
        'contact_number',
        'relationship_to_head',
        'house_no',
        'street_name',
        'purok',
        'resident_type',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'age' => 'integer',
        ];
    }

    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function purokRelation(): BelongsTo
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }
}
