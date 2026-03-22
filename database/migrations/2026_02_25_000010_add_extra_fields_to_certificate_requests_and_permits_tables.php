<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('certificate_requests', 'extra_fields')) {
                $table->json('extra_fields')->nullable()->after('purpose');
            }
        });

        Schema::table('permits', function (Blueprint $table) {
            if (! Schema::hasColumn('permits', 'extra_fields')) {
                $table->json('extra_fields')->nullable()->after('purpose');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_requests', 'extra_fields')) {
                $table->dropColumn('extra_fields');
            }
        });

        Schema::table('permits', function (Blueprint $table) {
            if (Schema::hasColumn('permits', 'extra_fields')) {
                $table->dropColumn('extra_fields');
            }
        });
    }
};
