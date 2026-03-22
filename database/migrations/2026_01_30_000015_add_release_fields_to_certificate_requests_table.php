<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds release tracking fields to certificate_requests table.
     */
    public function up(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('certificate_requests', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('certificate_requests', 'released_by')) {
                $table->foreignId('released_by')
                    ->nullable()
                    ->after('released_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_requests', 'released_by')) {
                $table->dropForeign(['released_by']);
                $table->dropColumn('released_by');
            }
            if (Schema::hasColumn('certificate_requests', 'released_at')) {
                $table->dropColumn('released_at');
            }
        });
    }
};
