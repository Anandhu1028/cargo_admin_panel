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
        Schema::table('calculation_details', function (Blueprint $table) {
             if (!Schema::hasColumn('calculation_details', 'customer_name')) {
            $table->string('customer_name')->nullable()->after('calculation_id');
        }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calculation_details', function (Blueprint $table) {
            //
        });
    }
};
