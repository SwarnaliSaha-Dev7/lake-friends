<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquor_servings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->index();
            $table->unsignedBigInteger('food_item_id')->index();
            $table->string('name');
            $table->integer('volume_ml');
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquor_servings');
    }
};
