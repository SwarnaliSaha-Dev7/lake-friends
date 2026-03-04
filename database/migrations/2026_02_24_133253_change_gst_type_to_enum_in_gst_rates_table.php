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
        Schema::table('gst_rates', function (Blueprint $table) {
            $table->enum('gst_type', ['restaurant', 'plan_purchase'])->after('gst_percentage')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gst_rates', function (Blueprint $table) {
            $table->string('gst_type', 255)->nullable()->after('gst_percentage')->change();
        });
    }
};
