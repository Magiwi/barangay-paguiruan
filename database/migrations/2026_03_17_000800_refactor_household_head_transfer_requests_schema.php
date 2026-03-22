<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_head_transfer_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('household_head_transfer_requests', 'household_id')) {
                $table->unsignedBigInteger('household_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'current_head_id')) {
                $table->unsignedBigInteger('current_head_id')->nullable()->after('household_id');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'new_head_id')) {
                $table->unsignedBigInteger('new_head_id')->nullable()->after('current_head_id');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'requested_by')) {
                $table->unsignedBigInteger('requested_by')->nullable()->after('new_head_id');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'requested_by_role')) {
                $table->string('requested_by_role', 20)->default('head')->after('requested_by');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'reason')) {
                $table->string('reason', 50)->nullable()->after('requested_by_role');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'details')) {
                $table->string('details', 255)->nullable()->after('reason');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'review_note')) {
                $table->string('review_note', 255)->nullable()->after('status');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('review_note');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
            if (! Schema::hasColumn('household_head_transfer_requests', 'pending_household_lock')) {
                $table->unsignedBigInteger('pending_household_lock')->nullable()->after('processed_at');
            }
        });

        DB::table('household_head_transfer_requests')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $householdId = $row->household_id;
                    if (! $householdId && ! empty($row->current_head_user_id)) {
                        $householdId = DB::table('households')
                            ->where('head_id', $row->current_head_user_id)
                            ->value('id');
                    }
                    if (! $householdId && ! empty($row->requester_user_id)) {
                        $householdId = DB::table('users')
                            ->where('id', $row->requester_user_id)
                            ->value('household_id');
                    }

                    DB::table('household_head_transfer_requests')
                        ->where('id', $row->id)
                        ->update([
                            'household_id' => $householdId,
                            'current_head_id' => $row->current_head_id ?? $row->current_head_user_id,
                            'new_head_id' => $row->new_head_id ?? $row->requested_head_user_id,
                            'requested_by' => $row->requested_by ?? $row->requester_user_id,
                            'reason' => $row->reason ?? $row->reason_code,
                            'details' => $row->details ?? $row->reason_details,
                            'review_note' => $row->review_note ?? $row->review_decision_notes,
                            'processed_by' => $row->processed_by ?? $row->reviewed_by_user_id,
                            'processed_at' => $row->processed_at ?? $row->reviewed_at,
                            'pending_household_lock' => ($row->status === 'pending' && $householdId)
                                ? (int) $householdId
                                : null,
                        ]);
                }
            });

        $duplicateHouseholds = DB::table('household_head_transfer_requests')
            ->select('household_id')
            ->where('status', 'pending')
            ->whereNotNull('household_id')
            ->groupBy('household_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('household_id');

        foreach ($duplicateHouseholds as $householdId) {
            $pendingIds = DB::table('household_head_transfer_requests')
                ->where('household_id', $householdId)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->pluck('id')
                ->all();

            $keepId = (int) array_shift($pendingIds);
            DB::table('household_head_transfer_requests')
                ->where('id', $keepId)
                ->update(['pending_household_lock' => (int) $householdId]);

            if ($pendingIds !== []) {
                DB::table('household_head_transfer_requests')
                    ->whereIn('id', $pendingIds)
                    ->update([
                        'status' => 'expired',
                        'review_note' => 'Auto-expired during migration because a household can only have one pending request.',
                        'processed_at' => now(),
                        'pending_household_lock' => null,
                    ]);
            }
        }

        Schema::table('household_head_transfer_requests', function (Blueprint $table): void {
            $table->foreign('household_id', 'hhtr_household_fk')->references('id')->on('households')->nullOnDelete();
            $table->foreign('current_head_id', 'hhtr_current_head_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('new_head_id', 'hhtr_new_head_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('requested_by', 'hhtr_requested_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('processed_by', 'hhtr_processed_by_fk')->references('id')->on('users')->nullOnDelete();

            $table->index(['household_id', 'status'], 'hhtr_household_status_idx');
            $table->index('processed_at', 'hhtr_processed_at_idx');
            $table->unique('pending_household_lock', 'hhtr_pending_household_unique');
        });
    }

    public function down(): void
    {
        Schema::table('household_head_transfer_requests', function (Blueprint $table): void {
            $table->dropUnique('hhtr_pending_household_unique');
            $table->dropIndex('hhtr_household_status_idx');
            $table->dropIndex('hhtr_processed_at_idx');

            $table->dropForeign('hhtr_household_fk');
            $table->dropForeign('hhtr_current_head_fk');
            $table->dropForeign('hhtr_new_head_fk');
            $table->dropForeign('hhtr_requested_by_fk');
            $table->dropForeign('hhtr_processed_by_fk');

            $table->dropColumn([
                'household_id',
                'current_head_id',
                'new_head_id',
                'requested_by',
                'requested_by_role',
                'reason',
                'details',
                'review_note',
                'processed_by',
                'processed_at',
                'pending_household_lock',
            ]);
        });
    }
};

