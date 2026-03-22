<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->string('category')->nullable()->after('subject');
            $table->string('attachment_path')->nullable()->after('description');
            $table->string('location')->nullable()->after('attachment_path');
            $table->foreignId('purok_id')->nullable()->after('location')->constrained('puroks')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable()->after('assigned_to');

            $table->index('category');
            $table->index('status');
        });

        Schema::create('complaint_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_report_id')->constrained('issue_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();

            $table->index('issue_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_notes');

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purok_id');
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropIndex(['category']);
            $table->dropIndex(['status']);
            $table->dropColumn(['category', 'attachment_path', 'location', 'closed_at']);
        });
    }
};
