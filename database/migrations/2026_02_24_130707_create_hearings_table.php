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
        Schema::create('hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotters')->cascadeOnDelete();
            $table->foreignId('summon_id')->constrained('summons')->cascadeOnDelete();
            $table->date('hearing_date');
            $table->time('hearing_time');
            $table->string('lupon_assigned');
            $table->string('complainant_attendance')->nullable();
            $table->string('respondent_attendance')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('summon_id');
            $table->index(['blotter_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearings');
    }
};
