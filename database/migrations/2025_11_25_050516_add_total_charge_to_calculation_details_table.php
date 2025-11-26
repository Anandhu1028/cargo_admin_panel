<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculation_details', function (Blueprint $table) {
            if (! Schema::hasColumn('calculation_details', 'total_charge')) {
                $table->decimal('total_charge', 15, 2)->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calculation_details', function (Blueprint $table) {
            if (Schema::hasColumn('calculation_details', 'total_charge')) {
                $table->dropColumn('total_charge');
            }
        });
    }
};
