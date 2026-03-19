<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE action_approvals MODIFY COLUMN module ENUM(
            'plan_purchase',
            'plan_renewal',
            'member_edit',
            'card_assign',
            'card_reassign',
            'food_price_update',
            'offer',
            'stock_adjustment',
            'member_create',
            'member_delete',
            'liquor_price_update',
            'liquor_item_create',
            'liquor_item_edit',
            'liquor_item_delete'
        ) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE action_approvals MODIFY COLUMN module ENUM(
            'plan_purchase',
            'plan_renewal',
            'member_edit',
            'card_assign',
            'card_reassign',
            'food_price_update',
            'offer',
            'stock_adjustment',
            'member_create',
            'member_delete'
        ) NULL");
    }
};
