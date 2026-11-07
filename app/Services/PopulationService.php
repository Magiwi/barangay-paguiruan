<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PopulationService
{
    /**
     * Approved accounts that appear in All Residents.
     * Pending, rejected, and deleted accounts are excluded. Super admin is excluded.
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

        $base = DB::query()->fromSub($approvedUsers, 'population_people');

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

