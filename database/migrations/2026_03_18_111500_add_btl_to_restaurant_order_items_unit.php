<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE restaurant_order_items MODIFY COLUMN unit ENUM('plate','ml','btl') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE restaurant_order_items MODIFY COLUMN unit ENUM('plate','ml') NULL");
    }
};
