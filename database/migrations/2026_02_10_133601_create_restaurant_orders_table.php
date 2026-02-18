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
        Schema::create('restaurant_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('wallet_transactions_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();

            $table->string('order_no',255)->nullable();
            $table->string('mr_no',255)->nullable();
            $table->string('bill_no',255)->nullable();

            $table->string('ac_head',255)->nullable();

            $table->decimal('taxable_amount',10,2)->nullable();
            $table->decimal('discount_amount',10,2)->nullable();
            $table->decimal('gst_percentage',5,2)->nullable();
            $table->decimal('gst_amount',10,2)->nullable();
            $table->decimal('net_amount',10,2)->nullable();

            $table->enum('status',['pending','paid','cancelled','refunded'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_orders');
    }
};
