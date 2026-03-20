<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `action_approvals` MODIFY `module` ENUM(
            'plan_purchase',
            'plan_renewal',
            'member_edit',
            'card_assign',
            'card_reassign',
            'food_price_update',
            'food_item_create',
            'food_item_update',
            'food_item_delete',
            'offer',
            'stock_adjustment',
            'member_create',
            'member_delete',
            'locker_purchase',
            'add_on_purchase',
            'liquor_item_create',
            'liquor_item_delete',
            'liquor_price_update',
            'liquor_serving_create',
            'liquor_serving_update',
            'liquor_serving_delete',
            'bar_stock_transfer'
        ) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `action_approvals` MODIFY `module` ENUM(
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
            'locker_purchase',
            'add_on_purchase',
            'liquor_item_create',
            'liquor_item_delete',
            'liquor_price_update',
            'liquor_serving_create',
            'liquor_serving_update',
            'liquor_serving_delete',
            'bar_stock_transfer'
        ) NULL");
    }
};
