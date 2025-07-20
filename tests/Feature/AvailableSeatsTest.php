<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Trip;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailableSeatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_available_seats()
    {
        // Create user, trip, and two stations
        $user = User::factory()->create();
        $trip = Trip::factory()->create();
        $stationA = Station::factory()->create();
        $stationB = Station::factory()->create();
        $trip->stations()->attach([
            $stationA->id => ['stop_order' => 1],
            $stationB->id => ['stop_order' => 2],
        ]);

        // Act as user and call the endpoint
        $response = $this->actingAs($user)->postJson('/api/available-seats', [
            'trip_id' => $trip->id,
            'from_station_id' => $stationA->id,
            'to_station_id' => $stationB->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['available_seats']);
    }
} 