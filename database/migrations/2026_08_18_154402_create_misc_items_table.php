<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('misc_items', function (Blueprint $table) {
            $table->id();
            // Not a real FK constraint — `clubs` is a MyISAM table and can't be
            // referenced by an InnoDB foreign key.
            $table->unsignedBigInteger('club_id')->nullable();
            $table->foreignId('misc_category_id')->nullable()->constrained('misc_categories')->nullOnDelete();
            $table->string('name', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('unit', 50)->nullable()->default('pcs');
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->boolean('is_price_editable')->default(false);
            $table->string('image', 255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['club_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('misc_items');
    }
};
