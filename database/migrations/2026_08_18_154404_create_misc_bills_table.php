<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('misc_bills', function (Blueprint $table) {
            $table->id();
            // Not a real FK constraint — `clubs` is a MyISAM table and can't be
            // referenced by an InnoDB foreign key.
            $table->unsignedBigInteger('club_id')->nullable();

            $table->string('bill_no', 255)->nullable();
            $table->string('mr_no', 255)->nullable();

            $table->string('buyer_name', 255)->nullable();
            $table->string('buyer_contact', 20)->nullable();
            $table->string('ac_head', 255)->nullable();

            $table->enum('payment_mode', ['cash', 'card', 'upi', 'bank_transfer', 'cheque', 'other'])->default('cash');
            $table->string('payment_reference', 100)->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);

            $table->enum('status', ['paid', 'cancelled'])->default('paid');
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            $table->index(['club_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('misc_bills');
    }
};
