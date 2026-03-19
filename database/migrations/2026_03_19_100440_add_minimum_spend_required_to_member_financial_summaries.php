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
        Schema::table('member_financial_summaries', function (Blueprint $table) {
            // Pro-rated minimum spend for this member in this FY
            // e.g. joined in January → ₹900 (3 months × ₹300), not full ₹3600
            $table->decimal('minimum_spend_required', 10, 2)->default(0)->after('financial_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('member_financial_summaries', function (Blueprint $table) {
            $table->dropColumn('minimum_spend_required');
        });
    }
};
