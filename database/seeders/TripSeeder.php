<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\Bus;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        Trip::create([
            'name' => 'Cairo to Asyut',
            'bus_id' => 1, 
        ]);

        Trip::create([
            'name' => 'Giza to AlMinya',
            'bus_id' => 2, 
        ]);
    }
}
