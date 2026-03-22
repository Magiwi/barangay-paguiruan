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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('head_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('purok_id')->nullable()->constrained('puroks')->nullOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();

            $table->date('birthdate')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('contact_number')->nullable();
            $table->string('relationship_to_head')->nullable();

            $table->string('house_no')->nullable();
            $table->string('street_name')->nullable();
            $table->string('purok')->nullable();
            $table->string('resident_type')->nullable();
            $table->timestamps();

            $table->index(['head_user_id', 'household_id']);
            $table->index('relationship_to_head');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
