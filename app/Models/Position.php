<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
        'max_seats',
        'sort_order',
    ];

    /**
     * Users currently assigned to this position.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Count active holders (staff/admin with this position).
     */
    public function activeHolderCount(?int $excludeUserId = null): int
    {
        $query = User::where('position_id', $this->id)
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN]);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->count();
    }

    /**
     * Check if this position has available seats.
     */
    public function hasAvailableSeat(?int $excludeUserId = null): bool
    {
        return $this->activeHolderCount($excludeUserId) < $this->max_seats;
    }

    /**
     * Remaining seat count.
     */
    public function remainingSeats(?int $excludeUserId = null): int
    {
        return max(0, $this->max_seats - $this->activeHolderCount($excludeUserId));
    }
}
