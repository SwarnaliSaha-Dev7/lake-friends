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
        Schema::table('food_items', function (Blueprint $table) {

            $table->dropForeign(['food_sub_category_id']);
            $table->dropColumn(['food_sub_category_id']);

            $table->foreignId('category_id')->nullable()->constrained('food_categories')->nullOnDelete()->after('club_id');

            $table->enum('item_type',['food','liquor'])->nullable()->after('category_id');

            $table->enum('unit',['plate','ml','bottle'])->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_items', function (Blueprint $table) {

            $table->dropColumn('item_type');

            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id']);

            $table->foreignId('food_sub_category_id')->nullable()->constrained('food_sub_categories')->nullOnDelete();

            $table->string('unit', 255)->nullable()->change();


        });
    }
};
