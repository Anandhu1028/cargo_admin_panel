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
        Schema::create('rate_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calculation_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index('calculation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_reports');
    }
};