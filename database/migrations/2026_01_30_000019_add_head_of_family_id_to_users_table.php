<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'head_of_family_id')) {
                $table->foreignId('head_of_family_id')
                    ->nullable()
                    ->after('head_of_family')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'family_link_status')) {
                $table->string('family_link_status')->nullable()->after('head_of_family_id');
                // Values: linked, unlinked, pending_link
            }
        });

        // Auto-link existing heads: heads have head_of_family='yes', set head_of_family_id=null (already default)
        // Non-heads with matching head names get linked in a later admin step
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'head_of_family_id')) {
                $table->dropConstrainedForeignId('head_of_family_id');
            }
            if (Schema::hasColumn('users', 'family_link_status')) {
                $table->dropColumn('family_link_status');
            }
        });
    }
};
