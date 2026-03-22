<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdPolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_head_resident_cannot_add_family_member(): void
    {
        $resident = $this->createResident([
            'head_of_family' => 'no',
            'head_of_family_id' => null,
            'status' => User::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($resident)->post(route('family.store'), [
            'first_name' => 'Kid',
            'last_name' => 'Resident',
            'birthdate' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'relationship_to_head' => 'son',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_cannot_manage_family_member_record_of_another_head(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResident(['head_of_family' => 'yes']);
        $household = Household::create(['head_id' => $head->id, 'purok' => $head->purok]);

        $member = FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'purok_id' => $head->purok_id,
            'first_name' => 'A',
            'last_name' => 'Member',
            'birthdate' => now()->subYears(9)->toDateString(),
            'age' => 9,
            'gender' => 'male',
            'relationship_to_head' => 'son',
            'house_no' => $head->house_no,
            'street_name' => $head->street_name,
            'purok' => $head->purok,
            'resident_type' => $head->resident_type,
        ]);

        $response = $this->actingAs($admin)->delete(route('family.destroy', $member));
        $response->assertForbidden();
    }

    private function createResident(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => 'Purok Policy']);

        $defaults = [
            'first_name' => 'Policy',
            'middle_name' => 'T',
            'last_name' => 'User',
            'suffix' => null,
            'house_no' => '7',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Policy St',
            'contact_number' => '+639191111111',
            'age' => 31,
            'gender' => 'male',
            'birthdate' => now()->subYears(31)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'email' => 'policy' . uniqid() . '@example.com',
            'password' => 'password123',
            'head_of_family_id' => null,
            'family_link_status' => null,
            'household_id' => null,
        ];

        $data = array_merge($defaults, array_intersect_key($overrides, $defaults));
        $user = User::create($data);
        $user->forceFill([
            'role' => $overrides['role'] ?? User::ROLE_RESIDENT,
            'status' => $overrides['status'] ?? User::STATUS_APPROVED,
            'is_suspended' => $overrides['is_suspended'] ?? false,
        ])->save();

        return $user->fresh();
    }
}
