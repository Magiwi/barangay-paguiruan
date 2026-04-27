<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->json('officials_snapshot')->nullable()->after('released_by');
        });

        Schema::table('permits', function (Blueprint $table) {
            $table->json('officials_snapshot')->nullable()->after('released_by');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->dropColumn('officials_snapshot');
        });

        Schema::table('permits', function (Blueprint $table) {
            $table->dropColumn('officials_snapshot');
        });
    }
};
