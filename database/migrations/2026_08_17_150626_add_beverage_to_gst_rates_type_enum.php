<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE gst_rates MODIFY gst_type ENUM('restaurant','plan_purchase','beverage') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE gst_rates MODIFY gst_type ENUM('restaurant','plan_purchase') NOT NULL");
    }
};
