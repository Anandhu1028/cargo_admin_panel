<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            $table->string('type')->default('standard')->after('total_amount'); // standard / lcl
            $table->json('charges_breakdown')->nullable()->after('type');       // itemized LCL data
            $table->decimal('per_cbm', 10, 2)->nullable()->after('charges_breakdown');
            $table->decimal('profit', 10, 2)->nullable()->after('per_cbm');
            $table->decimal('tax_amount', 10, 2)->nullable()->after('profit');
            $table->decimal('final_amount', 10, 2)->nullable()->after('tax_amount');
            $table->string('weight_unit')->default('kg')->after('final_amount');
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'charges_breakdown',
                'per_cbm',
                'profit',
                'tax_amount',
                'final_amount',
                'weight_unit',
            ]);
        });
    }
};
