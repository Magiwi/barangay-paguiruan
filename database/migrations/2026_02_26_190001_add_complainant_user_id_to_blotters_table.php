<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            if (! Schema::hasColumn('blotters', 'complainant_user_id')) {
                $table->foreignId('complainant_user_id')
                    ->nullable()
                    ->after('complainant_name')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            if (Schema::hasColumn('blotters', 'complainant_user_id')) {
                $table->dropConstrainedForeignId('complainant_user_id');
            }
        });
    }
};
