<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds resident classification fields for PWD, Senior Citizen,
     * and Registered Voter tagging with verification support.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Boolean flags (add only if not exists)
            if (! Schema::hasColumn('users', 'is_pwd')) {
                $table->boolean('is_pwd')->default(false)->after('is_suspended');
            }
            if (! Schema::hasColumn('users', 'is_senior')) {
                $table->boolean('is_senior')->default(false)->after('is_pwd');
            }
            if (! Schema::hasColumn('users', 'is_registered_voter')) {
                $table->boolean('is_registered_voter')->default(false)->after('is_senior');
            }

            // Verification status (for future approval workflow)
            if (! Schema::hasColumn('users', 'pwd_status')) {
                $table->string('pwd_status')->nullable()->after('is_registered_voter');
            }
            if (! Schema::hasColumn('users', 'senior_status')) {
                $table->string('senior_status')->nullable()->after('pwd_status');
            }

            // File path placeholders (for proof document uploads)
            if (! Schema::hasColumn('users', 'pwd_proof_path')) {
                $table->string('pwd_proof_path')->nullable()->after('senior_status');
            }
            if (! Schema::hasColumn('users', 'senior_proof_path')) {
                $table->string('senior_proof_path')->nullable()->after('pwd_proof_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'is_pwd',
                'is_senior',
                'is_registered_voter',
                'pwd_status',
                'senior_status',
                'pwd_proof_path',
                'senior_proof_path',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
