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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->cascadeOnDelete();
            $table->string('card_no',131)->nullable();
            $table->tinyInteger('status')->default(0)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'card_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
