<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'voter_status')) {
                $table->string('voter_status')->nullable()->after('senior_status');
            }

            if (! Schema::hasColumn('users', 'voter_proof_path')) {
                $table->string('voter_proof_path')->nullable()->after('senior_proof_path');
            }
        });

        // Normalize existing data so verification queues can distinguish missing proofs.
        DB::table('users')
            ->where('is_pwd', true)
            ->whereNull('pwd_status')
            ->update(['pwd_status' => DB::raw("CASE WHEN pwd_proof_path IS NULL THEN 'not_submitted' ELSE 'pending' END")]);

        DB::table('users')
            ->where('is_senior', true)
            ->whereNull('senior_status')
            ->update(['senior_status' => DB::raw("CASE WHEN senior_proof_path IS NULL THEN 'not_submitted' ELSE 'pending' END")]);

        DB::table('users')
            ->where('is_registered_voter', true)
            ->whereNull('voter_status')
            ->update(['voter_status' => 'not_submitted']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'voter_proof_path')) {
                $table->dropColumn('voter_proof_path');
            }

            if (Schema::hasColumn('users', 'voter_status')) {
                $table->dropColumn('voter_status');
            }
        });
    }
};
