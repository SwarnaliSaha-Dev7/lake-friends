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
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('wallet_transactions_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
