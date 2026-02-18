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
        Schema::create('entry_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();

            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();

            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();

            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();

            $table->tinyInteger('entry_status')->nullable()->default(0);

            $table->enum('deny_reason',['expired','blocked_card','insufficient_wallet','not_member','not_activated_card','not_activated_member'])->nullable();

            $table->decimal('fine_amount_calculated', 12, 2)->nullable();

            $table->date('membership_expiry_date_snapshot')->nullable();

            $table->dateTime('swiped_at')->nullable();

            $table->boolean('created_by_system')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_logs');
    }
};
