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
        Schema::table('locker_allocations', function (Blueprint $table) {
            $table->enum('status', ['active', 'pending', 'rejected'])
                ->default('pending')
                ->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locker_allocations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
