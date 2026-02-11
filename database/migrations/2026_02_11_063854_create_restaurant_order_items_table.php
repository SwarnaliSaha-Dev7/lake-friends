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
        Schema::create('restaurant_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_order_id')->nullable()->constrained('restaurant_orders')->nullOnDelete();
            $table->foreignId('food_item_id')->nullable()->constrained('food_items')->nullOnDelete();

            $table->string('quantity',255)->nullable();

            $table->enum('unit',['plate','ml'])->nullable();

            $table->decimal('unit_price',10,2)->nullable();

            $table->json('offer_applied')->nullable();

            $table->decimal('total_amount',10,2)->nullable();
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_items');
    }
};
