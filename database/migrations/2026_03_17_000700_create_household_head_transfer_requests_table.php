<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_head_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_head_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('requested_head_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason_code', 50);
            $table->string('reason_details', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_decision_notes', 255)->nullable();
            $table->unsignedBigInteger('processed_transfer_log_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'hhtr_status_created_idx');
            $table->index(['requester_user_id', 'status'], 'hhtr_requester_status_idx');
            $table->index('requested_head_user_id', 'hhtr_requested_head_idx');
            $table->index('reason_code', 'hhtr_reason_idx');
            $table->foreign('processed_transfer_log_id', 'hhtr_processed_log_fk')
                ->references('id')
                ->on('household_head_transfer_logs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_head_transfer_requests');
    }
};
