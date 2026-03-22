<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('household_reports');
    }

    public function down(): void
    {
        // Intentionally left blank; household report generator was rolled back.
    }
};
