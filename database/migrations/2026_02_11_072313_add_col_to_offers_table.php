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
        Schema::table('offers', function (Blueprint $table) {
            $table->decimal('discount_value', 8, 2)->default(0)->nullable()->after('name');
            $table->decimal('min_amount', 10, 2)->default(0)->nullable()->after('discount_value');
            $table->string('buy_qty')->nullable()->after('min_amount');
            $table->string('get_qty')->nullable()->after('buy_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'discount_value',
                'min_amount',
                'buy_qty',
                'get_qty'
            ]);
        });
    }
};
