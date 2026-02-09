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
        Schema::table('membership_purchase_histories', function (Blueprint $table) {
            $table->decimal('receipt_amount', 10, 2)->default(0)->after('fine_amount')->comment('Actual amount received after gst & all');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_purchase_histories', function (Blueprint $table) {
            $table->dropColumn(['receipt_amount']);
        });
    }
};
