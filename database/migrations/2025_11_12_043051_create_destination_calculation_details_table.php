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
        Schema::create('destination_calculation_details', function (Blueprint $table) {
            $table->id();
             $table->foreignId('calculation_id')->constrained('calculations')->onDelete('cascade');
            $table->string('customer_name')->nullable();
            $table->string('group_name')->nullable();   // e.g., LINER CHARGES, PORT CHARGES, etc.
            $table->string('particular')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('roe', 10, 4)->default(1);
            $table->decimal('amount', 15, 2)->default(0);
            $table->json('extra')->nullable(); // for notes like "36% of total value" or "1 day 120 Rs"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_calculation_details');
    }
};
