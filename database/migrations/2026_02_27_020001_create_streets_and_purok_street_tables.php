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
        if (! Schema::hasTable('streets')) {
            Schema::create('streets', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purok_street')) {
            Schema::create('purok_street', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purok_id')->constrained('puroks')->cascadeOnDelete();
                $table->foreignId('street_id')->constrained('streets')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['purok_id', 'street_id']);
            });
        }

        // Backfill existing user street_name values into streets and purok mappings.
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'street_name')) {
            $rows = DB::table('users')
                ->select('street_name', 'purok_id')
                ->whereNotNull('street_name')
                ->where('street_name', '!=', '')
                ->distinct()
                ->get();

            foreach ($rows as $row) {
                $streetName = trim((string) $row->street_name);
                if ($streetName === '') {
                    continue;
                }

                $streetId = DB::table('streets')->where('name', $streetName)->value('id');
                if (! $streetId) {
                    $streetId = DB::table('streets')->insertGetId([
                        'name' => $streetName,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($row->purok_id) {
                    DB::table('purok_street')->updateOrInsert(
                        ['purok_id' => $row->purok_id, 'street_id' => $streetId],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purok_street');
        Schema::dropIfExists('streets');
    }
};
