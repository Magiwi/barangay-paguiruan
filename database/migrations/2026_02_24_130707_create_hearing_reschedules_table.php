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
        if (Schema::hasTable('hearing_reschedules')) {
            return;
        }

        Schema::create('hearing_reschedules', function (Blueprint $table) {
            $table->id();
            // FK is added in a follow-up migration because this file shares
            // the same timestamp block as hearings creation.
            $table->unsignedBigInteger('hearing_id');
            $table->date('old_hearing_date');
            $table->time('old_hearing_time');
            $table->date('new_hearing_date');
            $table->time('new_hearing_time');
            $table->string('reason')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('hearing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearing_reschedules');
    }
};
