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
        Schema::create('stock_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable()->constrained('stock_warehouses')->nullOnDelete();
            $table->foreignId('food_items_id')->nullable()->constrained('food_items')->nullOnDelete();
            $table->date('snapshot_date')->nullable();
            $table->string('opening_quantity',255)->nullable();
            $table->string('in_quantity',255)->nullable();
            $table->string('out_quantity',255)->nullable();
            $table->string('closing_quantity',255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_snapshots');
    }
};
