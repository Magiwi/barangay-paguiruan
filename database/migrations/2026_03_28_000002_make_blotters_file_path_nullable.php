<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional document evidence may be omitted (video-only or salaysay-only uploads).
     */
    public function up(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
