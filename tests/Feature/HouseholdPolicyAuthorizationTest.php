<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class HouseholdPolicyAuthorizationTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_non_head_resident_cannot_add_family_member(): void
    {
        $resident = $this->createResidentUser([
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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
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

    public function test_guest_cannot_add_family_member(): void
    {
        $response = $this->post(route('family.store'), [
            'first_name' => 'Kid',
            'last_name' => 'Resident',
            'birthdate' => now()->subYears(10)->toDateString(),
            'gender' => 'male',
            'relationship_to_head' => 'son',
        ]);

        $response->assertRedirect(route('login'));
    }
}
