<?php

namespace Tests\Feature;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class PositionAssignmentGovernanceTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_position_change_requires_reason(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN]);
        $targetUser = $this->createResidentUser(['role' => User::ROLE_STAFF]);
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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN]);
        $targetUser = $this->createResidentUser(['role' => User::ROLE_STAFF]);
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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN]);
        $targetUser = $this->createResidentUser(['role' => User::ROLE_STAFF]);
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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN]);
        $position = Position::create([
            'name' => 'Barangay Chairman',
            'max_seats' => 1,
            'sort_order' => 1,
        ]);

        $holder = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'position_id' => $position->id,
        ]);
        $holder->forceFill([
            'position_id' => $position->id,
            'position_title' => $position->name,
        ])->save();

        $targetUser = $this->createResidentUser(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($admin)->post(route('admin.residents.updatePosition', $targetUser), [
            'position_id' => $position->id,
            'position_reason_code' => 'seat_reallocation',
            'position_reason_details' => 'Attempting to assign beyond seat limit',
        ]);

        $response->assertSessionHasErrors(['position_id']);
    }

    public function test_position_change_rejects_resident_target(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN]);
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);
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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN]);
        $superAdmin = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);
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

}
