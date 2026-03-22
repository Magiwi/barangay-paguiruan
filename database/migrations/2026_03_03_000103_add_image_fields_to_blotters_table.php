<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            if (! Schema::hasColumn('blotters', 'handwritten_salaysay_path')) {
                $table->string('handwritten_salaysay_path')->nullable()->after('file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            if (Schema::hasColumn('blotters', 'handwritten_salaysay_path')) {
                $table->dropColumn('handwritten_salaysay_path');
            }
        });
    }
};
