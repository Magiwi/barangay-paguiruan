<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_alert_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('registration_alert_runs', 'trigger_source')) {
                $table->string('trigger_source', 30)->default('scheduled')->after('command_name');
                $table->index(['trigger_source', 'ran_at']);
            }

            if (! Schema::hasColumn('registration_alert_runs', 'triggered_by_user_id')) {
                $table->foreignId('triggered_by_user_id')
                    ->nullable()
                    ->after('trigger_source')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('registration_alert_runs', 'trigger_reason')) {
                $table->string('trigger_reason', 255)->nullable()->after('triggered_by_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_alert_runs', function (Blueprint $table) {
            if (Schema::hasColumn('registration_alert_runs', 'trigger_reason')) {
                $table->dropColumn('trigger_reason');
            }

            if (Schema::hasColumn('registration_alert_runs', 'triggered_by_user_id')) {
                $table->dropConstrainedForeignId('triggered_by_user_id');
            }

            if (Schema::hasColumn('registration_alert_runs', 'trigger_source')) {
                $table->dropIndex(['trigger_source', 'ran_at']);
                $table->dropColumn('trigger_source');
            }
        });
    }
};
