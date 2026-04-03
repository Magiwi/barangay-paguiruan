<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\AuditLog;
use App\Models\Household;
use App\Models\HouseholdHeadTransferRequest;
use App\Models\Purok;
use App\Models\StaffPermission;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class HouseholdFamilyPhaseATest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_admin_must_provide_reason_when_linking_family_member(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $member = $this->createResidentUser(['head_of_family' => 'no']);

        $response = $this->actingAs($admin)->post(route('admin.residents.linkFamily', $member), [
            'head_of_family_id' => $head->id,
        ]);

        $response->assertSessionHasErrors(['transfer_reason_code']);
        $this->assertNull($member->fresh()->head_of_family_id);
    }

    public function test_admin_blocks_link_when_target_head_is_suspended(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $suspendedHead = $this->createResidentUser(['head_of_family' => 'yes', 'is_suspended' => true]);
        $member = $this->createResidentUser(['head_of_family' => 'no']);

        $response = $this->actingAs($admin)->post(route('admin.residents.linkFamily', $member), [
            'head_of_family_id' => $suspendedHead->id,
            'transfer_reason_code' => 'residence_update',
            'transfer_reason_details' => 'Validated transfer attempt.',
        ]);

        $response->assertSessionHasErrors(['head_of_family_id']);
        $this->assertNull($member->fresh()->head_of_family_id);
    }

    public function test_admin_can_unlink_member_when_reason_is_provided(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $member = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'relationship_to_head' => 'daughter',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.unlinkFamily', $member), [
            'transfer_reason_code' => 'correction_error',
            'transfer_reason_details' => 'Household profile correction.',
        ]);

        $response->assertRedirect(route('admin.residents.show', $member));
        $this->assertNull($member->fresh()->head_of_family_id);
        $this->assertSame('unlinked', $member->fresh()->family_link_status);
    }

    public function test_head_can_soft_delete_and_restore_member_within_window(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $member = FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'purok_id' => $head->purok_id,
            'first_name' => 'Mario',
            'last_name' => 'Santos',
            'birthdate' => now()->subYears(12)->toDateString(),
            'age' => 12,
            'gender' => 'male',
            'relationship_to_head' => 'son',
            'house_no' => $head->house_no,
            'street_name' => $head->street_name,
            'purok' => $head->purok,
            'resident_type' => $head->resident_type,
        ]);

        $deleteResponse = $this->actingAs($head)->delete(route('family.destroy', $member));
        $deleteResponse->assertRedirect(route('profile.show', ['tab' => 'family']));
        $this->assertSoftDeleted('family_members', ['id' => $member->id]);

        $restoreResponse = $this->actingAs($head)->post(route('family.restore', $member->id));
        $restoreResponse->assertRedirect(route('profile.show', ['tab' => 'family']));

        $this->assertDatabaseHas('family_members', [
            'id' => $member->id,
            'deleted_at' => null,
        ]);
    }

    public function test_head_cannot_restore_member_after_restore_window(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $member = FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'purok_id' => $head->purok_id,
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'birthdate' => now()->subYears(10)->toDateString(),
            'age' => 10,
            'gender' => 'female',
            'relationship_to_head' => 'daughter',
            'house_no' => $head->house_no,
            'street_name' => $head->street_name,
            'purok' => $head->purok,
            'resident_type' => $head->resident_type,
        ]);

        $this->actingAs($head)->delete(route('family.destroy', $member));

        FamilyMember::withTrashed()
            ->whereKey($member->id)
            ->update(['deleted_at' => now()->subDays(8)]);

        $restoreResponse = $this->actingAs($head)->post(route('family.restore', $member->id));
        $restoreResponse->assertSessionHasErrors(['family_restore']);
        $this->assertSoftDeleted('family_members', ['id' => $member->id]);
    }

    public function test_profile_family_tab_shows_linked_user_members_without_family_member_records(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $linkedUser = $this->createResidentUser([
            'first_name' => 'Linked',
            'last_name' => 'Resident',
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'relationship_to_head' => 'sister',
        ]);

        $response = $this->actingAs($head)
            ->get(route('profile.show', ['tab' => 'family']));

        $response->assertOk();
        $response->assertSeeText($linkedUser->first_name . ' ' . $linkedUser->middle_name . ' ' . $linkedUser->last_name);
        $response->assertSeeText('Linked account');
        $response->assertDontSeeText('No family members linked to you yet.');
    }

    public function test_resident_can_submit_head_transfer_request_with_reason(): void
    {
        $requestingHead = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Current',
            'last_name' => 'Head',
        ]);
        $household = Household::create([
            'head_id' => $requestingHead->id,
            'purok' => $requestingHead->purok,
        ]);

        $requestedHead = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $requestingHead->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'first_name' => 'Target',
            'last_name' => 'Member',
        ]);

        $response = $this->actingAs($requestingHead)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $requestedHead->id,
            'reason' => 'relocation',
            'details' => 'We transferred to another household.',
        ]);

        $response->assertRedirect(route('profile.show', ['tab' => 'family']));
        $this->assertDatabaseHas('household_head_transfer_requests', [
            'requested_by' => $requestingHead->id,
            'current_head_id' => $requestingHead->id,
            'new_head_id' => $requestedHead->id,
            'reason' => 'relocation',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $requestingHead->id,
            'type' => UserNotification::TYPE_HOUSEHOLD_TRANSFER,
            'title' => 'Head transfer request submitted',
        ]);
    }

    public function test_non_head_resident_cannot_submit_head_transfer_request(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $targetHead = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);
        $resident = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);

        $response = $this->actingAs($resident)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $targetHead->id,
            'reason' => 'relocation',
        ]);

        $response->assertSessionHasErrors(['requested_head_user_id']);
        $this->assertDatabaseMissing('household_head_transfer_requests', [
            'requested_by' => $resident->id,
            'new_head_id' => $targetHead->id,
        ]);
    }

    public function test_head_cannot_submit_transfer_request_when_no_eligible_linked_members(): void
    {
        $head = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Solo',
            'last_name' => 'Head',
        ]);
        $unrelated = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Other',
            'last_name' => 'Head',
        ]);

        $response = $this->actingAs($head)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $unrelated->id,
            'reason' => 'relocation',
            'details' => 'Need transfer.',
        ]);

        $response->assertSessionHasErrors(['requested_head_user_id']);
        $this->assertDatabaseMissing('household_head_transfer_requests', [
            'requested_by' => $head->id,
        ]);
    }

    public function test_head_transfer_request_requires_details_when_reason_is_other(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);
        $requestedHead = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);

        $response = $this->actingAs($head)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $requestedHead->id,
            'reason' => 'other',
            'details' => '',
        ]);

        $response->assertSessionHasErrors(['details']);
        $this->assertDatabaseMissing('household_head_transfer_requests', [
            'requested_by' => $head->id,
            'new_head_id' => $requestedHead->id,
        ]);
    }

    public function test_head_transfer_request_accepts_legacy_linked_account_from_family_member_record(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $legacyLinkedUser = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => null,
            'household_id' => null,
            'family_link_status' => null,
            'first_name' => 'Legacy',
            'last_name' => 'Linked',
        ]);

        FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'linked_user_id' => $legacyLinkedUser->id,
            'first_name' => $legacyLinkedUser->first_name,
            'middle_name' => $legacyLinkedUser->middle_name,
            'last_name' => $legacyLinkedUser->last_name,
            'suffix' => $legacyLinkedUser->suffix,
            'birthdate' => $legacyLinkedUser->birthdate,
            'age' => $legacyLinkedUser->age,
            'gender' => $legacyLinkedUser->gender,
            'contact_number' => $legacyLinkedUser->contact_number,
            'relationship_to_head' => 'daughter',
            'house_no' => $head->house_no,
            'purok' => $head->purok,
            'purok_id' => $head->purok_id,
            'resident_type' => $head->resident_type,
        ]);

        $response = $this->actingAs($head)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $legacyLinkedUser->id,
            'reason' => 'voluntary',
            'details' => 'Backfill legacy linked account.',
        ]);

        $response->assertRedirect(route('profile.show', ['tab' => 'family']));
        $this->assertDatabaseHas('household_head_transfer_requests', [
            'requested_by' => $head->id,
            'new_head_id' => $legacyLinkedUser->id,
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);
        $this->assertSame($head->id, $legacyLinkedUser->fresh()->head_of_family_id);
        $this->assertSame($household->id, $legacyLinkedUser->fresh()->household_id);
    }

    public function test_head_transfer_request_accepts_birthdate_eligible_member_with_stale_age_column(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);
        $requestedHead = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'birthdate' => now()->subYears(21)->toDateString(),
            'age' => 16,
        ]);

        $response = $this->actingAs($head)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $requestedHead->id,
            'reason' => 'voluntary',
            'details' => 'Birthdate proves legal age.',
        ]);

        $response->assertRedirect(route('profile.show', ['tab' => 'family']));
        $this->assertDatabaseHas('household_head_transfer_requests', [
            'requested_by' => $head->id,
            'new_head_id' => $requestedHead->id,
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);
    }

    public function test_household_can_only_have_one_pending_head_transfer_request(): void
    {
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);
        $firstTarget = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);
        $secondTarget = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);

        HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $head->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $head->id,
            'new_head_id' => $firstTarget->id,
            'reason' => 'voluntary',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($head)->post(route('profile.family-transfer-requests.store'), [
            'requested_head_user_id' => $secondTarget->id,
            'reason' => 'relocation',
            'details' => 'Trying to submit another pending request.',
        ]);

        $response->assertSessionHasErrors(['requested_head_user_id']);
        $this->assertSame(
            1,
            HouseholdHeadTransferRequest::query()
                ->where('household_id', $household->id)
                ->where('status', HouseholdHeadTransferRequest::STATUS_PENDING)
                ->count()
        );
    }

    public function test_admin_can_approve_resident_head_transfer_request(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);
        $requesterHead = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $requesterHead->id,
            'purok' => $requesterHead->purok,
        ]);
        $requestedMember = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $requesterHead->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);

        $request = HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $requesterHead->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $requesterHead->id,
            'new_head_id' => $requestedMember->id,
            'reason' => 'relocation',
            'details' => 'Moved to another home.',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.head-transfer-requests.approve', $request), [
            'review_note' => 'Verified documents.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('yes', $requestedMember->fresh()->head_of_family);
        $this->assertNull($requestedMember->fresh()->head_of_family_id);
        $this->assertSame($requestedMember->id, $requesterHead->fresh()->head_of_family_id);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_APPROVED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->processed_transfer_log_id);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $requesterHead->id,
            'type' => UserNotification::TYPE_HOUSEHOLD_TRANSFER,
            'title' => 'Head transfer request approved',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $requestedMember->id,
            'type' => UserNotification::TYPE_HOUSEHOLD_TRANSFER,
            'title' => 'You are now the head of family',
        ]);

        $auditDescription = (string) AuditLog::query()
            ->where('action', 'family_transfer_request_approved')
            ->where('target_id', $requesterHead->id)
            ->latest('id')
            ->value('description');
        $this->assertStringContainsString('reassigned_linked_users_count', $auditDescription);
        $this->assertStringContainsString('reassigned_family_records_count', $auditDescription);
    }

    public function test_staff_with_registrations_permission_can_approve_resident_head_transfer_request(): void
    {
        $staff = $this->createResidentUser([
            'role' => User::ROLE_STAFF,
            'head_of_family' => 'yes',
        ]);
        StaffPermission::create([
            'user_id' => $staff->id,
            'can_manage_registrations' => true,
            'can_manage_blotter' => false,
            'can_manage_announcements' => false,
            'can_manage_complaints' => false,
            'can_manage_reports' => false,
        ]);

        $requesterHead = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $requesterHead->id,
            'purok' => $requesterHead->purok,
        ]);
        $requestedMember = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $requesterHead->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);

        $request = HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $requesterHead->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $requesterHead->id,
            'new_head_id' => $requestedMember->id,
            'reason' => 'relocation',
            'details' => 'Moved out.',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($staff)->post(route('admin.head-transfer-requests.approve', $request), [
            'review_note' => 'Approved by staff with registration access.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('yes', $requestedMember->fresh()->head_of_family);
        $this->assertNull($requestedMember->fresh()->head_of_family_id);
        $this->assertSame($requestedMember->id, $requesterHead->fresh()->head_of_family_id);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_APPROVED, $request->fresh()->status);
    }

    public function test_admin_can_directly_transfer_head_to_linked_member_from_resident_profile(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);
        $currentHead = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Current',
            'last_name' => 'Head',
        ]);
        $household = Household::create([
            'head_id' => $currentHead->id,
            'purok' => $currentHead->purok,
        ]);
        $targetMember = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $currentHead->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'first_name' => 'Target',
            'last_name' => 'Member',
        ]);
        $otherMember = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $currentHead->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'first_name' => 'Other',
            'last_name' => 'Member',
        ]);

        $pendingRequest = HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $currentHead->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $currentHead->id,
            'new_head_id' => $otherMember->id,
            'reason' => 'voluntary',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.residents.transferHead', [$currentHead, $targetMember]), [
            'direct_transfer_reason' => 'Emergency correction.',
        ]);

        $response->assertRedirect(route('admin.residents.show', $currentHead));
        $this->assertSame('yes', $targetMember->fresh()->head_of_family);
        $this->assertNull($targetMember->fresh()->head_of_family_id);
        $this->assertSame($targetMember->id, $currentHead->fresh()->head_of_family_id);
        $this->assertSame($targetMember->id, $otherMember->fresh()->head_of_family_id);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_EXPIRED, $pendingRequest->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'direct_head_transfer',
            'target_id' => $currentHead->id,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $currentHead->id,
            'type' => UserNotification::TYPE_HOUSEHOLD_TRANSFER,
            'title' => 'Head of family updated',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $targetMember->id,
            'type' => UserNotification::TYPE_HOUSEHOLD_TRANSFER,
            'title' => 'You are now the head of family',
        ]);
    }

    public function test_staff_without_registrations_permission_cannot_access_or_process_transfer_queue(): void
    {
        $staff = $this->createResidentUser([
            'role' => User::ROLE_STAFF,
            'head_of_family' => 'yes',
        ]);
        StaffPermission::create([
            'user_id' => $staff->id,
            'can_manage_registrations' => false,
            'can_manage_blotter' => false,
            'can_manage_announcements' => false,
            'can_manage_complaints' => false,
            'can_manage_reports' => false,
        ]);

        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);
        $requestedMember = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);
        $request = HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $head->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $head->id,
            'new_head_id' => $requestedMember->id,
            'reason' => 'relocation',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.head-transfer-requests.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('admin.head-transfer-requests.approve', $request), [
                'review_note' => 'Should fail',
            ])
            ->assertForbidden();
    }

    public function test_transfer_queue_pending_tab_is_sorted_oldest_first(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);

        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $oldestRequestedMember = $this->createResidentUser([
            'first_name' => 'Oldest',
            'last_name' => 'Member',
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);
        $newestRequestedMember = $this->createResidentUser([
            'first_name' => 'Newest',
            'last_name' => 'Member',
            'head_of_family' => 'no',
            'head_of_family_id' => null,
            'household_id' => null,
            'family_link_status' => 'linked',
        ]);
        $newestHead = $this->createResidentUser([
            'first_name' => 'Newest',
            'last_name' => 'Head',
            'head_of_family' => 'yes',
        ]);
        $newestHousehold = Household::create([
            'head_id' => $newestHead->id,
            'purok' => $newestHead->purok,
        ]);
        $newestRequestedMember->forceFill([
            'head_of_family_id' => $newestHead->id,
            'household_id' => $newestHousehold->id,
        ])->save();

        $oldestRequest = HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $head->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $head->id,
            'new_head_id' => $oldestRequestedMember->id,
            'reason' => 'relocation',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
        $newestRequest = HouseholdHeadTransferRequest::create([
            'household_id' => $newestHousehold->id,
            'requested_by' => $newestHead->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $newestHead->id,
            'new_head_id' => $newestRequestedMember->id,
            'reason' => 'relocation',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.head-transfer-requests.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertSeeInOrder([
            $oldestRequestedMember->full_name,
            $newestRequestedMember->full_name,
        ]);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_PENDING, $oldestRequest->fresh()->status);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_PENDING, $newestRequest->fresh()->status);
    }

    public function test_transfer_queue_supports_search_and_date_filters(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);

        $headA = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Searchable',
            'last_name' => 'Head',
        ]);
        $householdA = Household::create([
            'head_id' => $headA->id,
            'purok' => $headA->purok,
        ]);
        $requestedA = $this->createResidentUser([
            'first_name' => 'Match',
            'last_name' => 'Keyword',
            'head_of_family' => 'no',
            'head_of_family_id' => $headA->id,
            'household_id' => $householdA->id,
            'family_link_status' => 'linked',
        ]);

        $headB = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Other',
            'last_name' => 'Head',
        ]);
        $householdB = Household::create([
            'head_id' => $headB->id,
            'purok' => $headB->purok,
        ]);
        $requestedB = $this->createResidentUser([
            'first_name' => 'No',
            'last_name' => 'Match',
            'head_of_family' => 'no',
            'head_of_family_id' => $headB->id,
            'household_id' => $householdB->id,
            'family_link_status' => 'linked',
        ]);

        HouseholdHeadTransferRequest::create([
            'household_id' => $householdA->id,
            'requested_by' => $headA->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $headA->id,
            'new_head_id' => $requestedA->id,
            'reason' => 'relocation',
            'details' => 'Keyword evidence details',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        HouseholdHeadTransferRequest::create([
            'household_id' => $householdB->id,
            'requested_by' => $headB->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $headB->id,
            'new_head_id' => $requestedB->id,
            'reason' => 'voluntary',
            'details' => 'Different details',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.head-transfer-requests.index', [
            'status' => 'all',
            'q' => 'Keyword',
            'from' => now()->subDays(4)->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSeeText($requestedA->full_name);
        $response->assertDontSeeText($requestedB->full_name);
    }

    public function test_rejecting_transfer_request_requires_rejection_note(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);
        $requestedMember = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
        ]);
        $request = HouseholdHeadTransferRequest::create([
            'household_id' => $household->id,
            'requested_by' => $head->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $head->id,
            'new_head_id' => $requestedMember->id,
            'reason' => 'relocation',
            'status' => HouseholdHeadTransferRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.head-transfer-requests.reject', $request), [
            'review_note' => '',
        ]);

        $response->assertSessionHasErrors(['review_note']);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_PENDING, $request->fresh()->status);
    }

    public function test_cannot_approve_already_processed_transfer_request(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $member = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'family_link_status' => 'linked',
        ]);
        $request = HouseholdHeadTransferRequest::create([
            'requested_by' => $head->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $head->id,
            'new_head_id' => $member->id,
            'reason' => 'voluntary',
            'status' => HouseholdHeadTransferRequest::STATUS_APPROVED,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.head-transfer-requests.approve', $request), [
            'review_note' => 'Second attempt',
        ]);

        $response->assertSessionHasErrors(['request']);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_APPROVED, $request->fresh()->status);
    }

    public function test_cannot_reject_already_processed_transfer_request(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
        ]);
        $head = $this->createResidentUser(['head_of_family' => 'yes']);
        $member = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'family_link_status' => 'linked',
        ]);
        $request = HouseholdHeadTransferRequest::create([
            'requested_by' => $head->id,
            'requested_by_role' => HouseholdHeadTransferRequest::REQUESTED_BY_ROLE_HEAD,
            'current_head_id' => $head->id,
            'new_head_id' => $member->id,
            'reason' => 'voluntary',
            'status' => HouseholdHeadTransferRequest::STATUS_REJECTED,
            'processed_by' => $admin->id,
            'processed_at' => now(),
            'review_note' => 'Already rejected',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.head-transfer-requests.reject', $request), [
            'review_note' => 'Second rejection',
        ]);

        $response->assertSessionHasErrors(['request']);
        $this->assertSame(HouseholdHeadTransferRequest::STATUS_REJECTED, $request->fresh()->status);
    }

    public function test_updating_head_profile_cascades_address_to_linked_members(): void
    {
        $admin = $this->createResidentUser(['role' => User::ROLE_ADMIN, 'head_of_family' => 'yes']);
        $oldPurok = Purok::create(['name' => 'Purok Old']);
        $newPurok = Purok::create(['name' => 'Purok New']);

        $head = $this->createResidentUser([
            'head_of_family' => 'yes',
            'purok_id' => $oldPurok->id,
            'purok' => $oldPurok->name,
            'house_no' => '12',
            'street_name' => 'Old Street',
        ]);

        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        $linkedUser = $this->createResidentUser([
            'head_of_family' => 'no',
            'head_of_family_id' => $head->id,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'purok_id' => $oldPurok->id,
            'purok' => $oldPurok->name,
            'house_no' => '12',
            'street_name' => 'Old Street',
        ]);

        $member = FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'purok_id' => $oldPurok->id,
            'first_name' => 'Lia',
            'last_name' => 'Santos',
            'birthdate' => now()->subYears(8)->toDateString(),
            'age' => 8,
            'gender' => 'female',
            'relationship_to_head' => 'daughter',
            'house_no' => '12',
            'street_name' => 'Old Street',
            'purok' => $oldPurok->name,
            'resident_type' => 'permanent',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.residents.update', $head), [
            'first_name' => $head->first_name,
            'middle_name' => $head->middle_name,
            'last_name' => $head->last_name,
            'suffix' => $head->suffix,
            'house_no' => '88A',
            'purok_id' => $newPurok->id,
            'contact_number' => '09171234567',
            'gender' => 'male',
            'birthdate' => now()->subYears(35)->toDateString(),
            'civil_status' => 'married',
            'resident_type' => 'non-permanent',
            'permanent_house_no' => '17',
            'permanent_street' => 'Permanent Street',
            'permanent_region' => 'Region III',
            'permanent_barangay' => 'Paguiruan',
            'permanent_city' => 'Floridablanca',
            'permanent_province' => 'Pampanga',
            'is_head' => 1,
            'household_id' => '',
            'relationship_to_head' => '',
        ]);

        $response->assertRedirect(route('admin.residents.show', $head));

        $linkedUser->refresh();
        $member->refresh();

        $this->assertSame('88A', $linkedUser->house_no);
        $this->assertSame($newPurok->id, $linkedUser->purok_id);
        $this->assertSame('non-permanent', $linkedUser->resident_type);

        $this->assertSame('88A', $member->house_no);
        $this->assertSame($newPurok->id, $member->purok_id);
        $this->assertSame('non-permanent', $member->resident_type);
    }

}
