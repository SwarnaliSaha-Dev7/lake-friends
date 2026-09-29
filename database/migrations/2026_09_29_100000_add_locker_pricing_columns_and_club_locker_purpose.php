<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locker_prices', function (Blueprint $table) {
            $table->decimal('club_first_price', 10, 2)->default(2500)->after('price');
            $table->decimal('club_renewal_price', 10, 2)->default(200)->after('club_first_price');
            $table->decimal('swim_price', 10, 2)->default(200)->after('club_renewal_price');
            $table->decimal('gst_percentage', 5, 2)->default(18)->after('swim_price');
        });

        // One settings row per club, so the Locker Price page always has something to edit.
        DB::statement("
            INSERT INTO locker_prices (club_id, price, is_active, club_first_price, club_renewal_price, swim_price, gst_percentage, created_at, updated_at)
            SELECT c.id, 0, 1, 2500, 200, 200, 18, NOW(), NOW()
            FROM clubs c
            WHERE NOT EXISTS (
                SELECT 1 FROM locker_prices lp WHERE lp.club_id = c.id AND lp.deleted_at IS NULL
            )
        ");

        DB::statement("ALTER TABLE payment_histories MODIFY purpose ENUM('plan_purchase','plan_renewal','recharge','fine','swim_locker_purchase','club_locker_purchase') NULL");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM payment_histories WHERE purpose = 'club_locker_purchase'");
        DB::statement("ALTER TABLE payment_histories MODIFY purpose ENUM('plan_purchase','plan_renewal','recharge','fine','swim_locker_purchase') NULL");

        Schema::table('locker_prices', function (Blueprint $table) {
            $table->dropColumn(['club_first_price', 'club_renewal_price', 'swim_price', 'gst_percentage']);
        });
    }
};
