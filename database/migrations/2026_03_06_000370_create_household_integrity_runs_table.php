<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_integrity_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command_name', 120);
            $table->string('trigger_source', 20)->default('manual');
            $table->unsignedInteger('invalid_head_links')->default(0);
            $table->unsignedInteger('orphan_family_members')->default(0);
            $table->unsignedInteger('household_issues')->default(0);
            $table->unsignedInteger('missing_head_assignment')->default(0);
            $table->unsignedInteger('invalid_connection_type')->default(0);
            $table->unsignedInteger('broken_member_head_linkage')->default(0);
            $table->unsignedInteger('fixes_applied')->default(0);
            $table->unsignedInteger('recipients_notified')->default(0);
            $table->string('status', 30)->default('issues_detected');
            $table->string('notes', 500)->nullable();
            $table->timestamp('ran_at');
            $table->timestamps();

            $table->index(['status', 'ran_at'], 'hir_status_ran_at_idx');
            $table->index('trigger_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_integrity_runs');
    }
};
