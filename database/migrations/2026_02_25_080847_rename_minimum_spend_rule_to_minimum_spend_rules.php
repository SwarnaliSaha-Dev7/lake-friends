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
        Schema::rename('minimum_spend_rule', 'minimum_spend_rules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('minimum_spend_rules', 'minimum_spend_rule');
    }
};
