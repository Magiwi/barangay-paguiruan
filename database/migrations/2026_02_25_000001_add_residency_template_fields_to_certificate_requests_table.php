<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->string('residency_years_text')->nullable()->after('purpose');
            $table->string('certificate_name_override')->nullable()->after('residency_years_text');
            $table->string('certificate_address_override')->nullable()->after('certificate_name_override');
            $table->date('certificate_issued_on')->nullable()->after('certificate_address_override');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_requests', function (Blueprint $table) {
            $table->dropColumn([
                'residency_years_text',
                'certificate_name_override',
                'certificate_address_override',
                'certificate_issued_on',
            ]);
        });
    }
};
