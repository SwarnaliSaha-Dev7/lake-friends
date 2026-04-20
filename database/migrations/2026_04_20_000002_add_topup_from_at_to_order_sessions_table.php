<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->timestamp('topup_from_at')
                ->nullable()
                ->after('opening_wallet_balance')
                ->comment('Reference timestamp to count wallet top-ups that occurred after this order session was created');
        });

        // Backfill existing rows so current behavior remains stable.
        DB::statement("UPDATE order_sessions SET topup_from_at = created_at WHERE topup_from_at IS NULL");
    }

    public function down(): void
    {
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->dropColumn('topup_from_at');
        });
    }
};

