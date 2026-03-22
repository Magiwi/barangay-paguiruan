<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_manage_blotter')->default(false);
            $table->boolean('can_manage_announcements')->default(false);
            $table->boolean('can_manage_complaints')->default(false);
            $table->boolean('can_manage_reports')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('position_title')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position_title');
        });
    }
};
