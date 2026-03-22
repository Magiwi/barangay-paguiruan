<?php

namespace App\Services;

use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PopulationService
{
    /**
     * Population base source:
     * - approved user accounts (excluding super admin via countable scope)
     * - non-linked family member records (to avoid duplicate linked users)
     */
    public function populationPeopleBaseQuery(?int $purokId = null): Builder
    {
        $approvedUsers = User::query()
            ->countable()
            ->where('status', User::STATUS_APPROVED)
            ->selectRaw("
                users.id as source_id,
                users.purok_id,
                users.resident_type,
                users.birthdate,
                users.gender
            ");

        $standaloneFamilyMembers = FamilyMember::query()
            ->whereNull('deleted_at')
            ->whereNull('linked_user_id')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users as heads')
                    ->whereColumn('heads.id', 'family_members.head_user_id')
                    ->where('heads.role', '!=', User::ROLE_SUPER_ADMIN);
            })
            ->selectRaw("
                family_members.id as source_id,
                family_members.purok_id,
                family_members.resident_type,
                family_members.birthdate,
                family_members.gender
            ");

        $base = DB::query()->fromSub(
            $approvedUsers->unionAll($standaloneFamilyMembers),
            'population_people'
        );

        if ($purokId !== null) {
            $base->where('purok_id', $purokId);
        }

        return $base;
    }

    public function getTotalResidents(?int $purokId = null): int
    {
        return (int) $this->populationPeopleBaseQuery($purokId)->count();
    }
}

