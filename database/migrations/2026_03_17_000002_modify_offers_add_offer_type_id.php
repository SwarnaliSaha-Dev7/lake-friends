<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('offer_type');
            $table->foreignId('offer_type_id')->nullable()->after('club_id')->constrained('offer_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['offer_type_id']);
            $table->dropColumn('offer_type_id');
            $table->enum('offer_type', ['b1g1', 'percent_discount', 'flat_discount'])->nullable()->after('club_id');
        });
    }
};
