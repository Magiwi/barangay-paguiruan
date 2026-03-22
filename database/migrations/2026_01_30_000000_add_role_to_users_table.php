<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds role column (resident, staff, admin) or updates existing enum to string.
     * Safe for existing users: default 'resident'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('resident')->after('password');
            }
        });

        // If role column exists as enum (e.g. from original migration), widen to string so staff/admin work.
        if (Schema::hasColumn('users', 'role') && Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::getConnection()->statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'resident'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
