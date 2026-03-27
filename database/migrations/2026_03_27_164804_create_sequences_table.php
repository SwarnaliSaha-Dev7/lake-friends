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
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->index();
            $table->string('sequence_name', 50); // session_no, order_no, mr_no, bill_no
            $table->string('fy_label', 10);       // e.g. 25-26
            $table->unsignedInteger('last_value')->default(0);
            $table->unique(['club_id', 'sequence_name', 'fy_label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
