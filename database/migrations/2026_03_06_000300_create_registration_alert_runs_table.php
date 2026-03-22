<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_alert_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command_name', 120);
            $table->unsignedSmallInteger('threshold_hours')->default(48);
            $table->unsignedInteger('overdue_count')->default(0);
            $table->unsignedInteger('due_soon_count')->default(0);
            $table->unsignedInteger('missing_id_count')->default(0);
            $table->unsignedInteger('recipients_targeted')->default(0);
            $table->unsignedInteger('recipients_sent')->default(0);
            $table->string('status', 40)->default('ok');
            $table->string('notes')->nullable();
            $table->timestamp('ran_at');
            $table->timestamps();

            $table->index(['status', 'ran_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_alert_runs');
    }
};
