<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rejected applicants are deleted; approval log rows must survive (SET NULL on user delete).
     */
    public function up(): void
    {
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('approval_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE approval_logs MODIFY user_id BIGINT UNSIGNED NULL');
        }

        Schema::table('approval_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::table('approval_logs')->whereNull('user_id')->delete();

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('approval_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        } else {
            DB::statement('ALTER TABLE approval_logs MODIFY user_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('approval_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
