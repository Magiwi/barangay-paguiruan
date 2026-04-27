<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            if (! Schema::hasColumn('blotters', 'video_path')) {
                $table->string('video_path')->nullable()->after('handwritten_salaysay_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            if (Schema::hasColumn('blotters', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });
    }
};
