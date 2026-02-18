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
        Schema::table('stock_snapshots', function (Blueprint $table) {

            $table->unsignedInteger('opening_quantity')->nullable()->change();
            $table->unsignedInteger('in_quantity')->nullable()->change();
            $table->unsignedInteger('out_quantity')->nullable()->change();
            $table->unsignedInteger('closing_quantity')->nullable()->change();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_snapshots', function (Blueprint $table) {

            $table->string('opening_quantity')->nullable()->change();
            $table->string('in_quantity')->nullable()->change();
            $table->string('out_quantity')->nullable()->change();
            $table->string('closing_quantity')->nullable()->change();
            
        });
    }
};
