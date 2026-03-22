<?php

namespace Tests\Feature;

use App\Models\Position;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionAssignmentGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_change_requires_reason(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $targetUser = $this->createResident(['role' => User::ROLE_STAFF]);
        $position = Position::create([
            'name' => 'Barangay Investigator',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $targetUser), [
            'position_id' => $position->id,
        ]);

        $response->assertSessionHasErrors(['position_reason_code']);
    }

    public function test_position_change_records_history_log(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $targetUser = $this->createResident(['role' => User::ROLE_STAFF]);
        $position = Position::create([
            'name' => 'Barangay Investigator',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $targetUser), [
            'position_id' => $position->id,
            'position_reason_code' => 'organizational_update',
            'position_reason_details' => 'Alignment with current committee assignments',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('position_change_logs', [
            'resident_user_id' => $targetUser->id,
            'new_position_id' => $position->id,
            'changed_by_user_id' => $admin->id,
            'reason_code' => 'organizational_update',
        ]);
    }

    public function test_position_change_other_reason_requires_details(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $targetUser = $this->createResident(['role' => User::ROLE_STAFF]);
        $position = Position::create([
            'name' => 'Barangay Treasurer',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $targetUser), [
            'position_id' => $position->id,
            'position_reason_code' => 'other',
            'position_reason_details' => 'x',
        ]);

        $response->assertSessionHasErrors(['position_reason_details']);
    }

    public function test_position_change_rejects_when_seat_is_full(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $position = Position::create([
            'name' => 'Barangay Chairman',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $holder = $this->createResident([
            'role' => User::ROLE_ADMIN,
            'position_id' => $position->id,
        ]);
        $holder->forceFill([
            'position_id' => $position->id,
            'position_title' => $position->name,
        ])->save();

        $targetUser = $this->createResident(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $targetUser), [
            'position_id' => $position->id,
            'position_reason_code' => 'seat_reallocation',
            'position_reason_details' => 'Attempting to assign beyond seat limit',
        ]);

        $response->assertSessionHasErrors(['position_id']);
    }

    public function test_position_change_rejects_resident_target(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $resident = $this->createResident(['role' => User::ROLE_RESIDENT]);
        $position = Position::create([
            'name' => 'Barangay Investigator',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $resident), [
            'position_id' => $position->id,
            'position_reason_code' => 'organizational_update',
            'position_reason_details' => 'Resident should not be assignable',
        ]);

        $response->assertSessionHasErrors(['position_id']);
    }

    public function test_position_change_404_for_super_admin_target(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN]);
        $superAdmin = $this->createResident(['role' => User::ROLE_RESIDENT]);
        $superAdmin->forceFill(['role' => User::ROLE_SUPER_ADMIN])->save();

        $position = Position::create([
            'name' => 'Barangay Investigator',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $superAdmin), [
            'position_id' => $position->id,
            'position_reason_code' => 'organizational_update',
            'position_reason_details' => 'Should be blocked',
        ]);

        $response->assertNotFound();
    }

    private function createResident(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => 'Purok Position']);

        $defaults = [
            'first_name' => 'Position',
            'middle_name' => 'T',
            'last_name' => 'User',
            'suffix' => null,
            'house_no' => '8',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Position St',
            'contact_number' => '+639189999999',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'email' => 'position' . uniqid() . '@example.com',
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
