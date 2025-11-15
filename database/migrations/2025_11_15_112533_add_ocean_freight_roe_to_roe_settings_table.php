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
        Schema::table('roe_settings', function (Blueprint $table) {
               $table->decimal('ocean_freight_roe', 10, 4)->default(0)->after('roe_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roe_settings', function (Blueprint $table) {
             $table->dropColumn('ocean_freight_roe');
        });
    }
};
