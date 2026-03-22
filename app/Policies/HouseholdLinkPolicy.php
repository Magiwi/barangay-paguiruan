<?php

namespace App\Policies;

use App\Models\User;

class HouseholdLinkPolicy
{
    public function manage(User $actor, User $resident): bool
    {
        if ($resident->role !== User::ROLE_RESIDENT) {
            return false;
        }

        if (in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            return true;
        }

        if ($actor->role === User::ROLE_STAFF) {
            return $actor->canAccess('registrations');
        }

        return false;
    }
}
