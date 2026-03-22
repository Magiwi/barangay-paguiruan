<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blotter_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotters')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('purpose');
            $table->string('status')->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['blotter_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blotter_requests');
    }
};
