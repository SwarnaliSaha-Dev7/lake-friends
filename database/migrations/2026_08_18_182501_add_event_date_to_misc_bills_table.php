<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('misc_bills', function (Blueprint $table) {
            // Nullable: most misc bills (T-Shirt, walk-in Table Tennis) are used the
            // same day they're billed. This is only filled in for advance bookings
            // (e.g. Banquet Rent booked today for a future date) — when null, the
            // bill's created_at date is the effective usage date.
            $table->date('event_date')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('misc_bills', function (Blueprint $table) {
            $table->dropColumn('event_date');
        });
    }
};
