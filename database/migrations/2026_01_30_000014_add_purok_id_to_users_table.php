<?php

use App\Models\Purok;
use App\Models\User;
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
        // Step 1: Add purok_id column
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'purok_id')) {
                $table->foreignId('purok_id')
                    ->nullable()
                    ->after('purok')
                    ->constrained('puroks')
                    ->nullOnDelete();
            }
        });

        // Step 2: Migrate existing purok strings to purok_id
        // Only if both tables exist and have data
        if (Schema::hasTable('puroks') && Schema::hasColumn('users', 'purok')) {
            $puroks = Purok::pluck('id', 'name');

            User::whereNotNull('purok')
                ->whereNull('purok_id')
                ->cursor()
                ->each(function ($user) use ($puroks) {
                    $purokName = $user->purok;

                    // Try exact match
                    if (isset($puroks[$purokName])) {
                        $user->purok_id = $puroks[$purokName];
                        $user->save();
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'purok_id')) {
                $table->dropForeign(['purok_id']);
                $table->dropColumn('purok_id');
            }
        });
    }
};
