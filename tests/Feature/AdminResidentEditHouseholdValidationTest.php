<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResidentEditHouseholdValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_head_cannot_be_assigned_to_existing_household(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $target = $this->createResident(['role' => User::ROLE_RESIDENT]);
        $head = $this->createResident([
            'role' => User::ROLE_RESIDENT,
            'head_of_family' => 'yes',
        ]);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $payload = $this->baseEditPayload($target, [
            'is_head' => 1,
            'household_id' => $household->id,
            'relationship_to_head' => '',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.residents.update', $target), $payload);

        $response->assertSessionHasErrors(['household_id']);
    }

    public function test_member_assignment_requires_relationship_to_head(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $target = $this->createResident(['role' => User::ROLE_RESIDENT]);
        $head = $this->createResident([
            'role' => User::ROLE_RESIDENT,
            'head_of_family' => 'yes',
        ]);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $payload = $this->baseEditPayload($target, [
            'household_id' => $household->id,
            'relationship_to_head' => '',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.residents.update', $target), $payload);

        $response->assertSessionHasErrors(['relationship_to_head']);
    }

    public function test_minor_member_cannot_use_adult_only_relationship(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $target = $this->createResident([
            'role' => User::ROLE_RESIDENT,
            'birthdate' => now()->subYears(16)->toDateString(),
            'age' => 16,
        ]);
        $head = $this->createResident([
            'role' => User::ROLE_RESIDENT,
            'head_of_family' => 'yes',
        ]);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $payload = $this->baseEditPayload($target, [
            'birthdate' => now()->subYears(16)->toDateString(),
            'household_id' => $household->id,
            'relationship_to_head' => 'spouse',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.residents.update', $target), $payload);

        $response->assertSessionHasErrors(['relationship_to_head']);
    }

    public function test_non_permanent_requires_permanent_address_fields(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $target = $this->createResident(['role' => User::ROLE_RESIDENT]);

        $payload = $this->baseEditPayload($target, [
            'resident_type' => 'non-permanent',
            'permanent_house_no' => '',
            'permanent_street' => '',
            'permanent_region' => '',
            'permanent_barangay' => '',
            'permanent_city' => '',
            'permanent_province' => '',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.residents.update', $target), $payload);

        $response->assertSessionHasErrors([
            'permanent_house_no',
            'permanent_street',
            'permanent_region',
            'permanent_barangay',
            'permanent_city',
            'permanent_province',
        ]);
    }

    public function test_switching_to_permanent_clears_permanent_address_fields(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $target = $this->createResident([
            'role' => User::ROLE_RESIDENT,
            'resident_type' => 'non-permanent',
            'permanent_house_no' => '77',
            'permanent_street' => 'Old Street',
            'permanent_region' => 'Region III',
            'permanent_barangay' => 'Old Barangay',
            'permanent_city' => 'Old City',
            'permanent_province' => 'Old Province',
        ]);

        $payload = $this->baseEditPayload($target, [
            'resident_type' => 'permanent',
            'permanent_house_no' => 'Should clear',
            'permanent_street' => 'Should clear',
            'permanent_region' => 'Should clear',
            'permanent_barangay' => 'Should clear',
            'permanent_city' => 'Should clear',
            'permanent_province' => 'Should clear',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.residents.update', $target), $payload);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'resident_type' => 'permanent',
            'permanent_house_no' => null,
            'permanent_street' => null,
            'permanent_region' => null,
            'permanent_barangay' => null,
            'permanent_city' => null,
            'permanent_province' => null,
        ]);
    }

    private function baseEditPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'suffix' => $user->suffix,
            'house_no' => $user->house_no,
            'purok_id' => $user->purok_id,
            'street_name' => $user->street_name,
            'contact_number' => $user->contact_number,
            'gender' => $user->gender,
            'birthdate' => optional($user->birthdate)->toDateString() ?? now()->subYears(30)->toDateString(),
            'civil_status' => $user->civil_status,
            'resident_type' => $user->resident_type,
            'household_id' => null,
            'relationship_to_head' => null,
        ], $overrides);
    }

    private function createResident(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => 'Purok Household Edit']);

        $defaults = [
            'first_name' => 'Household',
            'middle_name' => 'T',
            'last_name' => 'User',
            'suffix' => null,
            'house_no' => '11',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Household St',
            'contact_number' => '+639181111111',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'permanent_house_no' => null,
            'permanent_street' => null,
            'permanent_region' => null,
            'permanent_barangay' => null,
            'permanent_city' => null,
            'permanent_province' => null,
            'email' => 'household' . uniqid() . '@example.com',
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
