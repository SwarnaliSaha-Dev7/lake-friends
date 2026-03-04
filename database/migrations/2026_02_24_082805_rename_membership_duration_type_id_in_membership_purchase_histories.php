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
        Schema::table('membership_purchase_histories', function (Blueprint $table) {

            // Drop old foreign key
            $table->dropForeign(['membership_duration_type_id']);

            // Rename column
            $table->renameColumn('membership_duration_type_id','membership_plan_type_id');

            // Add new foreign key
           $table->foreignId('membership_plan_type_id')->nullable()->change()->constrained('membership_plan_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_purchase_histories', function (Blueprint $table) {

            $table->dropForeign(['membership_plan_type_id']);

            $table->renameColumn('membership_plan_type_id','membership_duration_type_id');

            $table->foreignId('membership_duration_type_id')->nullable()->change()->constrained('membership_duration_types')->nullOnDelete();
        });
    }
};
