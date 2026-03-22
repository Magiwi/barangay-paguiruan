<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_head_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('old_head_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_head_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('changed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 30);
            $table->string('reason_code', 50);
            $table->string('reason_details', 255)->nullable();
            $table->timestamps();

            $table->index(['resident_user_id', 'created_at'], 'hhtl_resident_created_idx');
            $table->index(['changed_by_user_id', 'created_at'], 'hhtl_actor_created_idx');
            $table->index('reason_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_head_transfer_logs');
    }
};
