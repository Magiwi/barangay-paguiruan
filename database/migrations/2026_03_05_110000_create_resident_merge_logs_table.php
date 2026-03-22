<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resident_merge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('secondary_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tables_payload')->nullable();
            $table->json('primary_snapshot')->nullable();
            $table->json('secondary_snapshot')->nullable();
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();

            $table->index(['primary_user_id', 'secondary_user_id']);
            $table->index('performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_merge_logs');
    }
};

