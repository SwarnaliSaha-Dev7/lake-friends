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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('membership_type_id')->nullable()->constrained('membership_types')->nullOnDelete();
            $table->foreignId('membership_duration_type_id')->nullable()->constrained('membership_duration_types')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable(); // NULL for lifetime
            $table->enum('status',['pending','active', 'expired', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
