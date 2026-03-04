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
        Schema::create('lockers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->string('locker_number',255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->enum('status',['available','occupied'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lockers');
    }
};
