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
        Schema::table('fine_rules', function (Blueprint $table) {
            $table->unsignedInteger('grace_days')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fine_rules', function (Blueprint $table) {
            $table->string('grace_days')->nullable()->change();
        });
    }
};
