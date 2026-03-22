<?php

namespace App\Services;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\HouseholdHeadTransferLog;
use App\Models\HouseholdHeadTransferRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HeadTransferService
{
    /**
     * Approve a resident-submitted transfer request with strict locking.
     */
    public function approveRequest(HouseholdHeadTransferRequest $request, User $actor, ?string $reviewNote = null): HouseholdHeadTransferRequest
    {
        return DB::transaction(function () use ($request, $actor, $reviewNote): HouseholdHeadTransferRequest {
            $lockedRequest = HouseholdHeadTransferRequest::query()
                ->whereKey($request->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedRequest) {
                throw new RuntimeException('Head transfer request could not be found.');
            }
            if ($lockedRequest->status !== HouseholdHeadTransferRequest::STATUS_PENDING) {
                throw new RuntimeException('This head transfer request was already processed.');
            }

            $resident = User::query()->whereKey($lockedRequest->current_head_id)->lockForUpdate()->first();
            $newHead = User::query()->whereKey($lockedRequest->new_head_id)->lockForUpdate()->first();
            if (! $resident || ! $newHead) {
                throw new RuntimeException('Cannot process this request because resident/head data is missing.');
            }

            $household = $this->resolveHeadHouseholdForUpdate($resident, (int) $lockedRequest->household_id);
            $this->assertTransferEligibility($resident, $newHead, $household, $lockedRequest->status);
            $before = $this->familyLinkSnapshot($resident);

            [$linkedMemberIds, $familyMemberRecordIds, $transferLog] = $this->applyTransfer(
                $resident,
                $newHead,
                $household,
                $actor,
                (string) $lockedRequest->reason,
                $lockedRequest->details
            );

            $lockedRequest->fill([
                'status' => HouseholdHeadTransferRequest::STATUS_APPROVED,
                'review_note' => $reviewNote,
                'processed_by' => $actor->id,
                'processed_at' => now(),
                'processed_transfer_log_id' => $transferLog->id,
            ])->save();

            $auditDetails = [
                'performed_by' => $actor->id,
                'affected_household' => $household->id,
                'old_head' => $resident->id,
                'new_head' => $newHead->id,
                'timestamp' => now()->toIso8601String(),
                'before' => $before,
                'after' => $this->familyLinkSnapshot($resident->fresh()),
                'reassigned_linked_users_count' => count($linkedMemberIds),
                'reassigned_linked_user_ids' => $linkedMemberIds,
                'reassigned_family_records_count' => count($familyMemberRecordIds),
                'reassigned_family_record_ids' => $familyMemberRecordIds,
            ];

            AuditService::log(
                'family_transfer_request_approved',
                $resident,
                'Head transfer request approved. Details: ' . json_encode($auditDetails)
            );

            NotificationService::notify(
                $resident,
                'Head transfer request approved',
                'Your request was approved. New head of family is now: ' . $newHead->full_name . '.',
                'household_transfer',
                $lockedRequest->id
            );

            NotificationService::notify(
                $newHead,
                'You are now the head of family',
                'A verified transfer request promoted you as the new head of family for your household.',
                'household_transfer',
                $lockedRequest->id
            );

            return $lockedRequest->fresh([
                'requester',
                'currentHead',
                'newHead',
                'processedBy',
                'processedTransferLog',
            ]);
        });
    }

    /**
     * Execute admin/staff direct head transfer override.
     */
    public function directTransfer(
        User $currentHead,
        User $newHead,
        User $actor,
        ?string $reason = null
    ): HouseholdHeadTransferLog {
        return DB::transaction(function () use ($currentHead, $newHead, $actor, $reason): HouseholdHeadTransferLog {
            $lockedCurrentHead = User::query()->whereKey($currentHead->id)->lockForUpdate()->first();
            $lockedNewHead = User::query()->whereKey($newHead->id)->lockForUpdate()->first();
            if (! $lockedCurrentHead || ! $lockedNewHead) {
                throw new RuntimeException('Cannot process transfer because resident/head data is missing.');
            }

            if ($lockedCurrentHead->id === $lockedNewHead->id) {
                throw new RuntimeException('Current head and new head cannot be the same resident.');
            }
            if (
                $lockedCurrentHead->role !== User::ROLE_RESIDENT
                || $lockedCurrentHead->head_of_family !== 'yes'
                || $lockedCurrentHead->head_of_family_id !== null
            ) {
                throw new RuntimeException('Current head is no longer a valid top-level head of family.');
            }
            if (
                $lockedNewHead->role !== User::ROLE_RESIDENT
                || $lockedNewHead->head_of_family !== 'no'
                || $lockedNewHead->status !== User::STATUS_APPROVED
                || ($lockedNewHead->is_suspended ?? false)
            ) {
                throw new RuntimeException('Selected member is not eligible to become head.');
            }
            if (($lockedNewHead->age ?? 0) < 18) {
                throw new RuntimeException('Selected member must be 18 years old or above.');
            }

            $household = $this->resolveHeadHouseholdForUpdate(
                $lockedCurrentHead,
                (int) ($lockedCurrentHead->household_id ?? 0)
            );
            if ((int) ($lockedNewHead->household_id ?? 0) !== (int) $household->id) {
                throw new RuntimeException('Selected member does not belong to the same household.');
            }
            if ((int) ($lockedNewHead->head_of_family_id ?? 0) !== (int) $lockedCurrentHead->id) {
                throw new RuntimeException('Selected member is no longer linked under the current head.');
            }

            [$linkedMemberIds, $familyMemberRecordIds, $transferLog] = $this->applyTransfer(
                $lockedCurrentHead,
                $lockedNewHead,
                $household,
                $actor,
                'other',
                $reason
            );

            HouseholdHeadTransferRequest::query()
                ->where('household_id', $household->id)
                ->where('status', HouseholdHeadTransferRequest::STATUS_PENDING)
                ->update([
                    'status' => HouseholdHeadTransferRequest::STATUS_EXPIRED,
                    'review_note' => 'Superseded by admin direct head transfer.',
                    'processed_by' => $actor->id,
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);

            AuditService::log(
                'direct_head_transfer',
                $lockedCurrentHead,
                'Direct head transfer override executed. Details: ' . json_encode([
                    'performed_by' => $actor->id,
                    'affected_household' => $household->id,
                    'old_head' => $lockedCurrentHead->id,
                    'new_head' => $lockedNewHead->id,
                    'reason' => $reason,
                    'timestamp' => now()->toIso8601String(),
                    'reassigned_linked_users_count' => count($linkedMemberIds),
                    'reassigned_family_records_count' => count($familyMemberRecordIds),
                ])
            );

            NotificationService::notify(
                $lockedCurrentHead,
                'Head of family updated',
                'The barangay office transferred your household head role to ' . $lockedNewHead->full_name . '.',
                'household_transfer',
                $transferLog->id
            );
            NotificationService::notify(
                $lockedNewHead,
                'You are now the head of family',
                'The barangay office assigned you as the head of family for your household.',
                'household_transfer',
                $transferLog->id
            );

            return $transferLog;
        });
    }

    private function applyTransfer(
        User $resident,
        User $newHead,
        Household $household,
        User $actor,
        string $reason,
        ?string $details
    ): array {
        $oldHeadId = (int) $resident->id;

        $linkedMemberIds = User::query()
            ->where('head_of_family_id', $oldHeadId)
            ->where('id', '!=', $newHead->id)
            ->lockForUpdate()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $familyMemberRecordIds = FamilyMember::query()
            ->where('head_user_id', $oldHeadId)
            ->lockForUpdate()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $newHead->fill([
            'head_of_family' => 'yes',
            'head_of_family_id' => null,
            'household_id' => $household->id,
            'family_link_status' => 'linked',
            'house_no' => $resident->house_no,
            'purok' => $resident->purok,
            'purok_id' => $resident->purok_id,
            'resident_type' => $resident->resident_type,
        ])->save();

        $resident->fill([
            'head_of_family' => 'no',
            'family_link_status' => 'linked',
            'head_of_family_id' => $newHead->id,
            'household_id' => $household->id,
        ])->save();

        if ($linkedMemberIds !== []) {
            User::query()
                ->whereIn('id', $linkedMemberIds)
                ->update([
                    'head_of_family_id' => $newHead->id,
                    'household_id' => $household->id,
                    'updated_at' => now(),
                ]);
        }

        if ($familyMemberRecordIds !== []) {
            FamilyMember::query()
                ->whereIn('id', $familyMemberRecordIds)
                ->update([
                    'head_user_id' => $newHead->id,
                    'household_id' => $household->id,
                    'updated_at' => now(),
                ]);
        }

        $household->fill([
            'head_id' => $newHead->id,
            'purok' => $newHead->purok ?? $resident->purok ?? $household->purok,
        ])->save();

        $transferLog = HouseholdHeadTransferLog::create([
            'resident_user_id' => $resident->id,
            'old_head_user_id' => $oldHeadId,
            'new_head_user_id' => $newHead->id,
            'changed_by_user_id' => $actor->id,
            'action' => HouseholdHeadTransferLog::ACTION_REASSIGN,
            'reason_code' => $reason,
            'reason_details' => $details,
        ]);

        return [$linkedMemberIds, $familyMemberRecordIds, $transferLog];
    }

    private function resolveHeadHouseholdForUpdate(User $head, int $requestedHouseholdId = 0): Household
    {
        $household = null;
        if ($requestedHouseholdId > 0) {
            $household = Household::query()->whereKey($requestedHouseholdId)->lockForUpdate()->first();
        }

        if (! $household) {
            $household = Household::query()->where('head_id', $head->id)->lockForUpdate()->first();
        }

        if (! $household) {
            $household = Household::create([
                'head_id' => $head->id,
                'purok' => $head->purok ?? '',
            ]);
            $household = Household::query()->whereKey($household->id)->lockForUpdate()->firstOrFail();
        } elseif (($head->purok ?? '') !== '' && $household->purok !== $head->purok) {
            $household->fill(['purok' => $head->purok])->save();
        }

        return $household;
    }

    private function assertTransferEligibility(User $resident, User $newHead, Household $household, string $requestStatus): void
    {
        if ($requestStatus !== HouseholdHeadTransferRequest::STATUS_PENDING) {
            throw new RuntimeException('This head transfer request was already processed.');
        }
        if ($newHead->id === $resident->id) {
            throw new RuntimeException('A resident cannot be linked to themselves.');
        }
        if ($resident->role !== User::ROLE_RESIDENT || $resident->head_of_family !== 'yes' || $resident->head_of_family_id !== null) {
            throw new RuntimeException('Requester must be an active top-level head of family.');
        }
        if ((int) ($newHead->head_of_family_id ?? 0) !== (int) $resident->id) {
            throw new RuntimeException('Requested member is not currently linked under this head.');
        }
        if ((int) ($newHead->household_id ?? 0) !== (int) $household->id) {
            throw new RuntimeException('Requested member does not belong to the same household.');
        }
        if ($newHead->role !== User::ROLE_RESIDENT || $newHead->head_of_family !== 'no') {
            throw new RuntimeException('Requested member is not eligible to become new head.');
        }
        if ($newHead->status !== User::STATUS_APPROVED || ($newHead->is_suspended ?? false)) {
            throw new RuntimeException('Requested member is not active.');
        }
        if (($newHead->age ?? 0) < 18) {
            throw new RuntimeException('Requested member must be 18 years old or above to become head.');
        }
        if ($resident->status !== User::STATUS_APPROVED || ($resident->is_suspended ?? false)) {
            throw new RuntimeException('Only active residents can transfer head roles.');
        }
        if ($this->hasAnotherActiveHeadInHousehold($resident, $household)) {
            throw new RuntimeException('Requester household has another active head and cannot be transferred automatically.');
        }
    }

    private function hasAnotherActiveHeadInHousehold(User $head, Household $household): bool
    {
        return User::query()
            ->where('role', User::ROLE_RESIDENT)
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false)
            ->where('household_id', $household->id)
            ->where('head_of_family', 'yes')
            ->whereNull('head_of_family_id')
            ->where('id', '!=', $head->id)
            ->exists();
    }

    private function familyLinkSnapshot(User $user): array
    {
        return [
            'head_of_family_id' => $user->head_of_family_id,
            'household_id' => $user->household_id,
            'head_of_family' => $user->head_of_family,
            'family_link_status' => $user->family_link_status,
            'relationship_to_head' => $user->relationship_to_head,
            'resident_type' => $user->resident_type,
        ];
    }
}

