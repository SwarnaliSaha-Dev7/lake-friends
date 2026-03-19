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
        Schema::create('member_fines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->nullable()->index();
            $table->unsignedBigInteger('member_id')->nullable()->index();
            $table->unsignedBigInteger('financial_year_id')->nullable()->index();

            // membership_expiry_fine | minimum_spend_shortfall
            $table->enum('fine_type', ['membership_expiry_fine', 'minimum_spend_shortfall']);

            $table->decimal('fine_amount', 10, 2);

            // For membership_expiry_fine: stores number of overdue days
            // For minimum_spend_shortfall: stores the spend shortfall amount
            $table->unsignedInteger('reference_days')->nullable();
            $table->decimal('reference_amount', 10, 2)->nullable();

            $table->date('fine_date');

            // pending = charged but not paid, paid = cleared at renewal, waived = manually waived
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_fines');
    }
};
