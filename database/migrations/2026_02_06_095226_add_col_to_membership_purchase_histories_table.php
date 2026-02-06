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
        Schema::table('membership_purchase_histories', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('id')->constrained('clubs')->nullOnDelete();
            $table->decimal('fee', 10, 2)->nullable()->default(0)->after('membership_duration_type_id');
            $table->decimal('fine_amount', 10, 2)->nullable()->default(0)->after('fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_purchase_histories', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropColumn(['club_id', 'fee', 'fine_amount']);
        });
    }
};
