<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blotter_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('blotter_requests', 'rejection_reason_code')) {
                $table->string('rejection_reason_code', 60)->nullable()->after('remarks');
                $table->index('rejection_reason_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blotter_requests', function (Blueprint $table) {
            if (Schema::hasColumn('blotter_requests', 'rejection_reason_code')) {
                $table->dropIndex(['rejection_reason_code']);
                $table->dropColumn('rejection_reason_code');
            }
        });
    }
};

