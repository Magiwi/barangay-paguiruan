<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_permissions', function (Blueprint $table) {
            $table->boolean('can_manage_registrations')->default(false)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('staff_permissions', function (Blueprint $table) {
            $table->dropColumn('can_manage_registrations');
        });
    }
};
