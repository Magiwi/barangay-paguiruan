<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if (! Schema::hasColumn('family_members', 'linked_user_id')) {
                $table->foreignId('linked_user_id')
                    ->nullable()
                    ->after('household_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('family_members', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            if (Schema::hasColumn('family_members', 'linked_user_id')) {
                $table->dropConstrainedForeignId('linked_user_id');
            }

            if (Schema::hasColumn('family_members', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
