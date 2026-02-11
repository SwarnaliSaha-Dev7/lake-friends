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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();

            $table->foreignId('action_approval_id')->constrained('action_approvals')->cascadeOnDelete();
            $table->enum('module', [
                'plan_purchase',
                'plan_renewal',
                'member_edit',
                'card_assign',
                'card_reassign',
                'price_update',
                'offer',
                'stock_adjustment'
            ])->nullable();
            $table->enum('action_type', ['create','update','delete'])->nullable();

            // $table->boolean('is_read')->default(false);
            // $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
