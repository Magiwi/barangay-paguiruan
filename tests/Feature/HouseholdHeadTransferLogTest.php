<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdHeadTransferLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class HouseholdHeadTransferLogTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_link_family_creates_structured_transfer_log(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $resident = $this->createResidentUser(['head_of_family' => 'no']);

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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $oldHead = $this->createResidentUser(['head_of_family' => 'yes']);
        $newHead = $this->createResidentUser(['head_of_family' => 'yes']);
        $oldHousehold = Household::create(['head_id' => $oldHead->id, 'purok' => $oldHead->purok]);

        $resident = $this->createResidentUser([
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
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $resident = $this->createResidentUser(['head_of_family' => 'no']);

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

    public function test_link_family_requires_head_of_family_id(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $resident = $this->createResidentUser(['head_of_family' => 'no']);

        $response = $this->actingAs($admin)->post(route('admin.residents.linkFamily', $resident), [
            'transfer_reason_code' => 'residence_update',
            'transfer_reason_details' => 'Missing head id.',
        ]);

        $response->assertSessionHasErrors(['head_of_family_id']);
    }
}
