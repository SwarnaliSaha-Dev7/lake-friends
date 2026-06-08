<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->string('order_person_name')->nullable()->after('member_id');
            $table->string('order_person_holder_type', 20)->nullable()->after('order_person_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_sessions', function (Blueprint $table) {
            $table->dropColumn(['order_person_name', 'order_person_holder_type']);
        });
    }
};
