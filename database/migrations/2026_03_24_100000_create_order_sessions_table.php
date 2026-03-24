<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('session_no')->unique();
            $table->enum('status', ['open', 'billed', 'cancelled'])->default('open');
            $table->decimal('taxable_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(10.00);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->string('bill_no')->nullable();
            $table->string('mr_no')->nullable();
            $table->unsignedBigInteger('wallet_transactions_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sessions');
    }
};
