<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bus;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        Bus::create(['plate_number' => 'BUS-001']);
        Bus::create(['plate_number' => 'BUS-002']);
    }
}
