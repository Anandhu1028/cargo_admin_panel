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
    Schema::table('calculations', function (Blueprint $table) {
        if (!Schema::hasColumn('calculations', 'customer_id')) {
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
        }
        if (!Schema::hasColumn('calculations', 'customer_name')) {
            $table->string('customer_name')->nullable();
        }
    });
}

};
