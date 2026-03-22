<?php

namespace App\Policies;

use App\Models\FamilyMember;
use App\Models\User;

class FamilyMemberPolicy
{
    public function create(User $user): bool
    {
        return $user->canManageOwnFamily();
    }

    public function update(User $user, FamilyMember $member): bool
    {
        return $user->canManageOwnFamily()
            && (int) $member->head_user_id === (int) $user->id;
    }

    public function delete(User $user, FamilyMember $member): bool
    {
        return $this->update($user, $member);
    }

    public function restore(User $user, FamilyMember $member): bool
    {
        return $this->update($user, $member);
    }

}
