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
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable()->constrained('stock_warehouses')->nullOnDelete();
            $table->foreignId('food_items_id')->nullable()->constrained('food_items')->nullOnDelete();
            $table->enum('movement_type',['opening','purchase','sale','adjustment','wastage'])->nullable();
            $table->enum('direction',['in','out'])->nullable();
            $table->string('quantity',255)->nullable();
            $table->enum('reference_type',['order','manual','purchase'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
