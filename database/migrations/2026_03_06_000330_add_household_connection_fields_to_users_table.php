<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'household_connection_type')) {
                $table->string('household_connection_type', 60)->nullable()->after('relationship_to_head');
                $table->index('household_connection_type');
            }

            if (! Schema::hasColumn('users', 'connection_note')) {
                $table->string('connection_note', 255)->nullable()->after('household_connection_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'connection_note')) {
                $table->dropColumn('connection_note');
            }

            if (Schema::hasColumn('users', 'household_connection_type')) {
                $table->dropIndex(['household_connection_type']);
                $table->dropColumn('household_connection_type');
            }
        });
    }
};
