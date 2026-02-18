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
        Schema::table('clubs', function (Blueprint $table) {
            $table->enum('financial_year_end_month',['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'])->nullable()->default('3')->after('financial_year_start_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('financial_year_end_month');
        });
    }
};
