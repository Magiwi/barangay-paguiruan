<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('id_verified_in_person')->default(false)->after('government_id_path');
            $table->foreignId('registered_by')
                ->nullable()
                ->after('id_verified_in_person')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registered_by');
            $table->dropColumn('id_verified_in_person');
        });
    }
};
