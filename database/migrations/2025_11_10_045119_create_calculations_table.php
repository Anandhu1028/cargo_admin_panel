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
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
             $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('from_pincode', 10)->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_pincode', 10)->nullable();
            $table->string('to_location')->nullable();
            $table->string('port')->nullable();
            $table->decimal('cbm', 10, 3)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('actual_weight', 10, 2)->nullable();
            $table->enum('condition', ['new','used'])->nullable();
            $table->boolean('additional_packing')->default(false);
            $table->decimal('volumetric_weight', 10, 2)->nullable();
            $table->decimal('chargeable_weight', 10, 2)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->json('breakdown')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
