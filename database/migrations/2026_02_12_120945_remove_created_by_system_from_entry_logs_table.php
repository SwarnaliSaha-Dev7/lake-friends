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
        Schema::table('entry_logs', function (Blueprint $table) {
            $table->dropColumn('created_by_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entry_logs', function (Blueprint $table) {
            $table->boolean('created_by_system')->default(true)->after('swiped_at');
        });
    }
};
