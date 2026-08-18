<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE gst_rates MODIFY gst_type ENUM('restaurant','plan_purchase','beverage','misc') NOT NULL");

        // Seed a default Misc GST row (0%) for every club that doesn't already
        // have one, so it always shows up on the GST Rate page ready to edit —
        // matches every other gst_type, which is one row per club.
        DB::statement("
            INSERT INTO gst_rates (club_id, gst_percentage, gst_type, created_at, updated_at)
            SELECT c.id, 0, 'misc', NOW(), NOW()
            FROM clubs c
            WHERE NOT EXISTS (
                SELECT 1 FROM gst_rates g WHERE g.club_id = c.id AND g.gst_type = 'misc'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DELETE FROM gst_rates WHERE gst_type = 'misc'");
        DB::statement("ALTER TABLE gst_rates MODIFY gst_type ENUM('restaurant','plan_purchase','beverage') NOT NULL");
    }
};
