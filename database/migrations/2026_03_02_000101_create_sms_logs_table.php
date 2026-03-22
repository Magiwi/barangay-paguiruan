<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mobile', 20);
            $table->string('template_key')->nullable();
            $table->text('message');
            $table->string('status', 20); // sent, failed, skipped
            $table->string('provider', 50)->nullable();
            $table->text('provider_response')->nullable();
            $table->string('context_type', 50)->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['template_key', 'created_at']);
            $table->index(['context_type', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
