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
        Schema::table('membership_plans', function (Blueprint $table) {
            
            $table->foreignId('membership_type_id')->nullable()->after('club_id')->constrained('membership_types')->nullOnDelete();

            $table->decimal('price', 10, 2)->default(0)->after('is_lifetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {

            $table->dropForeign(['membership_type_id']);
            $table->dropColumn(['membership_type_id', 'price']);
        });
    }
};
