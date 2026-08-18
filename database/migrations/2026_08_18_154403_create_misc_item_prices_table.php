<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('misc_item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('misc_item_id')->constrained('misc_items')->cascadeOnDelete();
            $table->decimal('price', 12, 2)->default(0);
            $table->dateTime('effective_from')->nullable();
            $table->dateTime('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['misc_item_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('misc_item_prices');
    }
};
