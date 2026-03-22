<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdHeadTransferLog;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdHeadTransferLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_family_creates_structured_transfer_log(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResident(['head_of_family' => 'yes']);
        $resident = $this->createResident(['head_of_family' => 'no']);

        $response = $this->actingAs($admin)->post(route('admin.residents.linkFamily', $resident), [
            'head_of_family_id' => $head->id,
            'transfer_reason_code' => 'residence_update',
            'transfer_reason_details' => 'Resident moved household.',
        ]);

        $response->assertRedirect(route('admin.residents.show', $resident));

        $this->assertDatabaseHas('household_head_transfer_logs', [
            'resident_user_id' => $resident->id,
            'old_head_user_id' => null,
            'new_head_user_id' => $head->id,
            'changed_by_user_id' => $admin->id,
            'action' => HouseholdHeadTransferLog::ACTION_LINK,
            'reason_code' => 'residence_update',
        ]);
    }

    public function test_reassign_family_creates_reassign_action_log(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $oldHead = $this->createResident(['head_of_family' => 'yes']);
        $newHead = $this->createResident(['head_of_family' => 'yes']);
        $oldHousehold = Household::create(['head_id' => $oldHead->id, 'purok' => $oldHead->purok]);

        $resident = $this->createResident([
            'head_of_family' => 'no',
            'head_of_family_id' => $oldHead->id,
            'household_id' => $oldHousehold->id,
            'family_link_status' => 'linked',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.linkFamily', $resident), [
            'head_of_family_id' => $newHead->id,
            'transfer_reason_code' => 'correction_error',
            'transfer_reason_details' => 'Initial head link was wrong.',
        ]);

        $response->assertRedirect(route('admin.residents.show', $resident));

        $this->assertDatabaseHas('household_head_transfer_logs', [
            'resident_user_id' => $resident->id,
            'old_head_user_id' => $oldHead->id,
            'new_head_user_id' => $newHead->id,
            'changed_by_user_id' => $admin->id,
            'action' => HouseholdHeadTransferLog::ACTION_REASSIGN,
            'reason_code' => 'correction_error',
        ]);
    }

    public function test_other_reason_code_requires_details(): void
    {
        $admin = $this->createResident(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResident(['head_of_family' => 'yes']);
        $resident = $this->createResident(['head_of_family' => 'no']);

        $response = $this->actingAs($admin)->post(route('admin.residents.linkFamily', $resident), [
            'head_of_family_id' => $head->id,
            'transfer_reason_code' => 'other',
            'transfer_reason_details' => '',
        ]);

        $response->assertSessionHasErrors(['transfer_reason_details']);
        $this->assertDatabaseMissing('household_head_transfer_logs', [
            'resident_user_id' => $resident->id,
            'reason_code' => 'other',
        ]);
    }

    private function createResident(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => 'Purok A']);

        $defaults = [
            'first_name' => 'Test',
            'middle_name' => 'A',
            'last_name' => 'Resident',
            'suffix' => null,
            'house_no' => '15',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Sample St',
            'contact_number' => '+639171234568',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'email' => 'qa' . uniqid() . '@example.com',
            'password' => 'password123',
            'head_of_family_id' => null,
            'family_link_status' => null,
            'household_id' => null,
            'relationship_to_head' => null,
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
