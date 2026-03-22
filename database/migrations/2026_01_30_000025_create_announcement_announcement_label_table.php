<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_announcement_label', function (Blueprint $table) {
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('announcement_label_id')->constrained('announcement_labels')->cascadeOnDelete();
            $table->primary(['announcement_id', 'announcement_label_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_announcement_label');
    }
};
