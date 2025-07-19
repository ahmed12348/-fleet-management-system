<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\Station;
use App\Models\TripStation;

class TripStationSeeder extends Seeder
{
    public function run(): void
    {
        // 🚍 Trip 1: Cairo → AlFayyum → AlMinya → Asyut
        $trip1 = Trip::where('name', 'Cairo to Asyut')->first();
        $stations1 = ['Cairo', 'AlFayyum', 'AlMinya', 'Asyut'];

        foreach ($stations1 as $index => $name) {
            $station = Station::where('name', $name)->first();
            if ($station && $trip1) {
                TripStation::create([
                    'trip_id'    => $trip1->id,
                    'station_id' => $station->id,
                    'stop_order' => $index + 1,
                ]);
            } else {
                echo "⚠️ Station or trip not found: $name\n";
            }
        }

        // 🚍 Trip 2: Giza → AlFayyum → AlMinya
        $trip2 = Trip::where('name', 'Giza to AlMinya')->first();
        $stations2 = ['Giza', 'AlFayyum', 'AlMinya'];

        foreach ($stations2 as $index => $name) {
            $station = Station::where('name', $name)->first();
            if ($station && $trip2) {
                TripStation::create([
                    'trip_id'    => $trip2->id,
                    'station_id' => $station->id,
                    'stop_order' => $index + 1,
                ]);
            } else {
                echo "⚠️ Station or trip not found: $name\n";
            }
        }
    }
}
