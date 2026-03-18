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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('direction', ['credit', 'debit'])->nullable();
            $table->enum('txn_type',['recharge','plan_purchase', 'add_on_purchase', 'plan_renewal', 'spend', 'refund', 'fine', 'adjustment', 'reversal','locker_purchase'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
