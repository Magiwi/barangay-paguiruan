<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\User;
use App\Services\PopulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class TotalResidentsCountTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_total_counts_approved_accounts_and_skips_pending_deleted_and_family_records(): void
    {
        $resident = $this->createResidentUser(['last_name' => 'ApprovedResident']);
        $this->createResidentUser([
            'role' => User::ROLE_STAFF,
            'last_name' => 'ApprovedStaff',
            'email' => 'staff.' . uniqid() . '@example.com',
        ]);
        $this->createAdminUser(['last_name' => 'ApprovedAdmin']);
        $this->createResidentUser([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_APPROVED,
            'last_name' => 'Super',
            'email' => 'super.' . uniqid() . '@example.com',
        ]);
        $pending = $this->createResidentUser([
            'status' => User::STATUS_PENDING,
            'last_name' => 'PendingResident',
            'email' => 'pending.' . uniqid() . '@example.com',
        ]);
        $deleted = $this->createResidentUser([
            'last_name' => 'DeletedResident',
            'email' => 'deleted.' . uniqid() . '@example.com',
        ]);

        $household = Household::create([
            'head_id' => $resident->id,
            'purok' => $resident->purok,
        ]);
        FamilyMember::create([
            'head_user_id' => $resident->id,
            'household_id' => $household->id,
            'purok_id' => $resident->purok_id,
            'first_name' => 'No',
            'last_name' => 'Account',
            'birthdate' => now()->subYears(10)->toDateString(),
            'age' => 10,
            'gender' => 'male',
            'relationship_to_head' => 'son',
            'house_no' => $resident->house_no,
            'street_name' => $resident->street_name,
            'purok' => $resident->purok,
            'resident_type' => $resident->resident_type,
        ]);

        $deleted->delete();

        $total = app(PopulationService::class)->getTotalResidents();

        $this->assertSame(3, $total);
        $this->assertDatabaseMissing('users', ['id' => $deleted->id]);
        $this->assertDatabaseHas('users', ['id' => $pending->id, 'status' => User::STATUS_PENDING]);
    }
}
