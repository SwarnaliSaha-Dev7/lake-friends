<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);          // e.g. B1G1, PERCENTAGE, FLAT
            $table->string('slug', 100)->unique(); // b1g1, percentage, flat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_types');
    }
};
