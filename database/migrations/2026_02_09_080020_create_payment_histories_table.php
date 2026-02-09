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
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->cascadeOnDelete();

            $table->enum('purpose',['plan_purchase','plan_renewal', 'recharge', 'fine'])->nullable();
            $table->foreignId('membership_purchase_history_id')->nullable()->constrained('membership_purchase_histories')->nullOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();

            $table->string('mr_no')->nullable();
            $table->string('bill_no')->nullable();
            $table->string('ac_head')->nullable();

            $table->decimal('taxable_amount', 10, 2)->nullable();
            $table->decimal('gst_percentage', 5, 2)->nullable();
            $table->decimal('gst_amount', 10, 2)->nullable();
            $table->decimal('gross_amount', 10, 2)->nullable();
            $table->string('payment_mode',100)->nullable();
            $table->enum('payment_status',['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->string('bank_name')->nullable();
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};
