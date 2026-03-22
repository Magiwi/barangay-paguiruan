<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Generate slugs for existing announcements
        $announcements = DB::table('announcements')->get();
        foreach ($announcements as $announcement) {
            $baseSlug = Str::slug($announcement->title) ?: 'announcement';
            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('announcements')->where('slug', $slug)->where('id', '!=', $announcement->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            DB::table('announcements')->where('id', $announcement->id)->update(['slug' => $slug]);
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
