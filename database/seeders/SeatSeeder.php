<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bus;
use App\Models\Seat;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
        $buses = Bus::all();

        foreach ($buses as $bus) {
            foreach (range(1, 12) as $number) {
                Seat::create([
                    'bus_id' => $bus->id,
                    'seat_number' => $number,
                ]);
            }
        }
    }
}
