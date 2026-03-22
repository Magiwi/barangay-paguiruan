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
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->text('resolution_notes')->nullable()->after('remarks');
            $table->string('action_taken', 100)->nullable()->after('resolution_notes');
            $table->string('after_photo_path')->nullable()->after('action_taken');
            $table->text('other_details')->nullable()->after('after_photo_path');
            $table->timestamp('resolved_at')->nullable()->after('closed_at');
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn([
                'resolution_notes',
                'action_taken',
                'after_photo_path',
                'other_details',
                'resolved_at',
            ]);
        });
    }
};
