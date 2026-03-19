<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfferTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Buy 1 Get 1 (B1G1)', 'slug' => 'b1g1'],
            ['name' => 'Percentage Discount',  'slug' => 'percentage'],
            ['name' => 'Flat Discount',         'slug' => 'flat'],
        ];

        foreach ($types as $type) {
            DB::table('offer_types')->insertOrIgnore($type + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
