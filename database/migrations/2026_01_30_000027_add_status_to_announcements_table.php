<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('is_published');
        });

        // Data transition: migrate is_published → status
        DB::table('announcements')
            ->where('is_published', true)
            ->update(['status' => 'approved']);

        DB::table('announcements')
            ->where('is_published', false)
            ->update(['status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
