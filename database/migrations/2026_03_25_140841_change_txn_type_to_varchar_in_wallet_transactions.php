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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('txn_type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->enum('txn_type', [
                'recharge', 'plan_purchase', 'add_on_purchase', 'plan_renewal',
                'spend', 'refund', 'fine', 'adjustment', 'reversal',
                'locker_purchase', 'locker_purchase_refund',
            ])->change();
        });
    }
};
