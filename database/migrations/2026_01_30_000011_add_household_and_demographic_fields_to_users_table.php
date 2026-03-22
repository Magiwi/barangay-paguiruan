<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('resident_type')->constrained('households')->nullOnDelete();
            $table->string('relationship_to_head')->nullable()->after('household_id');

            $table->boolean('is_pwd')->default(false)->after('status');
            $table->boolean('is_senior')->default(false)->after('is_pwd');
            $table->boolean('is_voter')->default(false)->after('is_senior');
            $table->string('voter_precinct')->nullable()->after('is_voter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropColumn([
                'household_id',
                'relationship_to_head',
                'is_pwd',
                'is_senior',
                'is_voter',
                'voter_precinct',
            ]);
        });
    }
};

