<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('household_integrity_runs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('household_integrity_runs', function ($table) {
            $table->id();
            $table->string('command_name')->default('households:integrity-check');
            $table->string('trigger_source')->default('manual');
            $table->unsignedInteger('invalid_head_links')->default(0);
            $table->unsignedInteger('orphan_family_members')->default(0);
            $table->unsignedInteger('household_issues')->default(0);
            $table->unsignedInteger('missing_head_assignment')->default(0);
            $table->unsignedInteger('invalid_connection_type')->default(0);
            $table->unsignedInteger('broken_member_head_linkage')->default(0);
            $table->unsignedInteger('fixes_applied')->default(0);
            $table->unsignedInteger('recipients_notified')->default(0);
            $table->string('status')->default('healthy');
            $table->text('notes')->nullable();
            $table->timestamp('ran_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('trigger_source');
            $table->index('ran_at');
        });
    }
};
