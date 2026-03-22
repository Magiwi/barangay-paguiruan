<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! $this->indexExists('users', 'users_household_name_idx')) {
                $table->index(['last_name', 'first_name', 'middle_name'], 'users_household_name_idx');
            }

            if (! $this->indexExists('users', 'users_household_filter_idx')) {
                $table->index(['status', 'head_of_family', 'purok_id'], 'users_household_filter_idx');
            }

            if (! $this->indexExists('users', 'users_status_idx')) {
                $table->index('status', 'users_status_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_household_name_idx')) {
                $table->dropIndex('users_household_name_idx');
            }

            if ($this->indexExists('users', 'users_household_filter_idx')) {
                $table->dropIndex('users_household_filter_idx');
            }

            if ($this->indexExists('users', 'users_status_idx')) {
                $table->dropIndex('users_status_idx');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        return match ($driver) {
            'mysql' => ! empty(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])),
            'sqlite' => collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($row) => ($row->name ?? null) === $index),
            'pgsql' => ! empty(DB::select('SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?', [$table, $index])),
            default => false,
        };
    }
};
