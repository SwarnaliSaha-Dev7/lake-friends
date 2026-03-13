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
        Schema::create('log_changes', function (Blueprint $table) {
            $table->id();
            $table->string('model')->nullable();
            $table->unsignedBigInteger('model_id');
            $table->string('action')->nullable(); // 'created' or 'updated'
            $table->json('changes')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('changed_from')->nullable(); // e.g., 'admin', 'parent'
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_changes');
    }
};
