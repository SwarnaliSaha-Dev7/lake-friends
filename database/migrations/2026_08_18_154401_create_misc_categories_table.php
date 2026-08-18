<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('misc_categories', function (Blueprint $table) {
            $table->id();
            // Not a real FK constraint — `clubs` is a MyISAM table and can't be
            // referenced by an InnoDB foreign key.
            $table->unsignedBigInteger('club_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('misc_categories');
    }
};
