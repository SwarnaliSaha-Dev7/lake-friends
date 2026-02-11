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
        Schema::create('member_financial_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years')->nullOnDelete();

            $table->decimal('total_recharge', 12, 2)->default(0);
            $table->decimal('total_spend', 12, 2)->default(0);

            $table->decimal('shortfall_amount', 12, 2)->default(0);
            $table->decimal('forfeited_amount', 12, 2)->default(0);
            $table->decimal('carry_forward_amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_financial_summaries');
    }
};
