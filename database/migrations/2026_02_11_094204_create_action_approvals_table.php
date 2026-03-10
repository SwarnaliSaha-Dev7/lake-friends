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
        Schema::create('action_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
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

            $table->enum('action_type', ['create', 'update', 'delete'])->nullable();
            $table->string('entity_model', 255)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->foreignId('membership_type_id')->constrained('membership_types')->cascadeOnDelete()->nullable();
            $table->unsignedBigInteger('maker_user_id')->nullable();
            $table->unsignedBigInteger('checker_user_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('request_payload')->nullable();
            $table->timestamp('approved_or_rejected_at')->nullable();
            $table->longText('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_approvals');
    }
};
