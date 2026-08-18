<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('misc_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('misc_bill_id')->constrained('misc_bills')->cascadeOnDelete();
            $table->foreignId('misc_item_id')->nullable()->constrained('misc_items')->nullOnDelete();

            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(0);

            // total_amount is the pre-GST line total (quantity * unit_price), same
            // semantics as restaurant_order_items.total_amount.
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('misc_bill_items');
    }
};
