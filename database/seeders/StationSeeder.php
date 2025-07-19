<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = ['Cairo', 'Giza', 'AlFayyum', 'AlMinya', 'Asyut'];

        foreach ($stations as $name) {
            Station::create(['name' => $name]);
        }
    }
}
