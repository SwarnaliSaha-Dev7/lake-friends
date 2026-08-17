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
        DB::statement("ALTER TABLE food_items MODIFY item_type ENUM('food','liquor','beverage') NULL");
        DB::statement("ALTER TABLE food_categories MODIFY item_type ENUM('food','liquor','beverage') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE food_items MODIFY item_type ENUM('food','liquor') NULL");
        DB::statement("ALTER TABLE food_categories MODIFY item_type ENUM('food','liquor') NOT NULL");
    }
};
