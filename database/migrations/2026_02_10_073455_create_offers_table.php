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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->enum('offer_type',['b1g1','percent_discount','flat_discount'])->nullable();
            $table->enum('applies_to',['food','liquor','both'])->nullable();
            $table->string('name',255)->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->enum('status',['draft','pending','active','expired','rejected'])->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
