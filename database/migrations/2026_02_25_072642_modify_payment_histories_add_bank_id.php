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
        Schema::table('payment_histories', function (Blueprint $table) {

            $table->dropColumn('bank_name');

            $table->foreignId('bank_id')->nullable()->after('wallet_transaction_id')->constrained('banks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_histories', function (Blueprint $table) {

            $table->dropForeign(['bank_id']);

            $table->dropColumn('bank_id');

            $table->string('bank_name',255)->nullable();
        });
    }
};
