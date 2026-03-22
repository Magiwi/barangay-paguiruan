<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Head of family name (when head_of_family = no)
            $table->string('head_first_name')->nullable()->after('head_of_family');
            $table->string('head_middle_name')->nullable()->after('head_first_name');
            $table->string('head_last_name')->nullable()->after('head_middle_name');
            // Permanent address (when resident_type = non-permanent)
            $table->string('permanent_house_no')->nullable()->after('resident_type');
            $table->string('permanent_street')->nullable()->after('permanent_house_no');
            $table->string('permanent_barangay')->nullable()->after('permanent_street');
            $table->string('permanent_city')->nullable()->after('permanent_barangay');
            $table->string('permanent_province')->nullable()->after('permanent_city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'head_first_name', 'head_middle_name', 'head_last_name',
                'permanent_house_no', 'permanent_street', 'permanent_barangay',
                'permanent_city', 'permanent_province',
            ]);
        });
    }
};
