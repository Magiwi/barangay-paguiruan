<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if (! $this->indexExists('family_members', 'family_members_head_deleted_idx')) {
                $table->index(['head_user_id', 'deleted_at'], 'family_members_head_deleted_idx');
            }

            if (! $this->indexExists('family_members', 'family_members_purok_idx')) {
                $table->index('purok_id', 'family_members_purok_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if ($this->indexExists('family_members', 'family_members_head_deleted_idx')) {
                $table->dropIndex('family_members_head_deleted_idx');
            }

            if ($this->indexExists('family_members', 'family_members_purok_idx')) {
                $table->dropIndex('family_members_purok_idx');
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
