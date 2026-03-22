<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_registered_voter')) {
                $table->dropColumn('is_registered_voter');
            }
            if (Schema::hasColumn('users', 'voter_status')) {
                $table->dropColumn('voter_status');
            }
            if (Schema::hasColumn('users', 'voter_proof_path')) {
                $table->dropColumn('voter_proof_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_registered_voter')) {
                $table->boolean('is_registered_voter')->default(false)->after('is_senior');
            }
            if (! Schema::hasColumn('users', 'voter_status')) {
                $table->string('voter_status')->nullable()->after('senior_status');
            }
            if (! Schema::hasColumn('users', 'voter_proof_path')) {
                $table->string('voter_proof_path')->nullable()->after('senior_proof_path');
            }
        });
    }
};
