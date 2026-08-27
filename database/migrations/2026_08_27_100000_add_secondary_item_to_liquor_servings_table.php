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
        Schema::table('liquor_servings', function (Blueprint $table) {
            // Optional mixer (e.g. soda) whose stock is deducted alongside the base
            // spirit when this cocktail is sold, but which never appears as its own
            // bill line -- the cocktail's price already bakes in the mixer's cost.
            $table->unsignedBigInteger('secondary_food_item_id')->nullable()->after('food_item_id');
            $table->integer('secondary_quantity')->nullable()->after('secondary_food_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liquor_servings', function (Blueprint $table) {
            $table->dropColumn(['secondary_food_item_id', 'secondary_quantity']);
        });
    }
};
