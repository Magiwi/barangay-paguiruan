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
        Schema::create('summons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotters')->cascadeOnDelete();
            $table->unsignedTinyInteger('summon_number');
            $table->date('hearing_date');
            $table->time('hearing_time');
            $table->string('lupon_assigned');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['blotter_id', 'summon_number']);
            $table->index(['blotter_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summons');
    }
};
