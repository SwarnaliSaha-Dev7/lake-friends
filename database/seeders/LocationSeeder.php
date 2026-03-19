<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::firstOrCreate(['name' => Location::GODOWN]);
        Location::firstOrCreate(['name' => Location::BAR]);
    }
}
