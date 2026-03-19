<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_ledgers MODIFY COLUMN movement_type ENUM(
            'opening','purchase','sale','adjustment','wastage','transfer'
        ) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_ledgers MODIFY COLUMN movement_type ENUM(
            'opening','purchase','sale','adjustment','wastage'
        ) NULL");
    }
};
