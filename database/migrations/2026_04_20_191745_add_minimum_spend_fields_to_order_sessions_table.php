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
            $table->decimal('minimum_spend_required', 10, 2)->default(0)->after('cancelled_by');
            $table->decimal('total_spend', 10, 2)->default(0)->after('minimum_spend_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->dropColumn(['minimum_spend_required', 'total_spend']);
        });
    }
};
