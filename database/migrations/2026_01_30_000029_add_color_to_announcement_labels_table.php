<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcement_labels', function (Blueprint $table) {
            $table->string('color')->default('bg-gray-100 text-gray-800')->after('slug');
        });

        // Backfill colors for existing labels
        $map = [
            'emergency' => 'bg-red-100 text-red-800',
            'health' => 'bg-green-100 text-green-800',
            'ayuda' => 'bg-yellow-100 text-yellow-800',
            'advisory' => 'bg-blue-100 text-blue-800',
            'event' => 'bg-purple-100 text-purple-800',
        ];

        foreach ($map as $slug => $color) {
            DB::table('announcement_labels')->where('slug', $slug)->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        Schema::table('announcement_labels', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
