<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blotter_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blotter_id')->constrained('blotters')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40)->default('updated');
            $table->text('change_note')->nullable();
            $table->json('changed_fields')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->timestamps();

            $table->index(['blotter_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blotter_revisions');
    }
};

