<?php

namespace Tests\Unit;

use App\Models\Trip;
use App\Models\Bus;
use App\Models\TripStation;
use App\Models\Booking;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_belongs_to_bus()
    {
        $bus = Bus::factory()->create();
        $trip = Trip::factory()->create(['bus_id' => $bus->id]);
        $this->assertInstanceOf(Bus::class, $trip->bus);
        $this->assertEquals($bus->id, $trip->bus->id);
    }

    public function test_trip_has_many_trip_stations()
    {
        $trip = Trip::factory()->create();
        $station = Station::factory()->create();
        $tripStation = TripStation::create([
            'trip_id' => $trip->id,
            'station_id' => $station->id,
            'stop_order' => 1,
        ]);
        $this->assertTrue($trip->tripStations->contains($tripStation));
    }

    public function test_trip_has_many_bookings()
    {
        $trip = Trip::factory()->create();
        $booking = Booking::factory()->create(['trip_id' => $trip->id]);
        $this->assertTrue($trip->bookings->contains($booking));
    }

    public function test_trip_belongs_to_many_stations()
    {
        $trip = Trip::factory()->create();
        $station = Station::factory()->create();
        $trip->stations()->attach($station->id, ['stop_order' => 1]);
        $this->assertTrue($trip->stations->contains($station));
    }
} 