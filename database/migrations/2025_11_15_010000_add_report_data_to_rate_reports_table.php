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
        Schema::table('rate_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('rate_reports', 'report_data')) {
                $table->json('report_data')->nullable()->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_reports', function (Blueprint $table) {
            if (Schema::hasColumn('rate_reports', 'report_data')) {
                $table->dropColumn('report_data');
            }
        });
    }
};
