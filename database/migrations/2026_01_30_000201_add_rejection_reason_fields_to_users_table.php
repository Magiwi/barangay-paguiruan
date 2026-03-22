<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'rejection_reason_code')) {
                $table->string('rejection_reason_code', 80)->nullable()->after('status');
                $table->index('rejection_reason_code');
            }

            if (! Schema::hasColumn('users', 'rejection_reason_details')) {
                $table->text('rejection_reason_details')->nullable()->after('rejection_reason_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'rejection_reason_details')) {
                $table->dropColumn('rejection_reason_details');
            }

            if (Schema::hasColumn('users', 'rejection_reason_code')) {
                $table->dropIndex(['rejection_reason_code']);
                $table->dropColumn('rejection_reason_code');
            }
        });
    }
};
