<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_page_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 64)->unique();
            $table->json('draft_sections')->nullable();
            $table->json('published_sections')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_page_layouts');
    }
};
