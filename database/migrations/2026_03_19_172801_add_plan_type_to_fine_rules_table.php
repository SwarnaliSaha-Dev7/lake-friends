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
        Schema::table('fine_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_plan_type_id')->nullable()->after('club_id');
            $table->foreign('membership_plan_type_id')
                  ->references('id')->on('membership_plan_types')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fine_rules', function (Blueprint $table) {
            $table->dropForeign(['membership_plan_type_id']);
            $table->dropColumn('membership_plan_type_id');
        });
    }
};
