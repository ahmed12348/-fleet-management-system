<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Trip;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trip_id' => Trip::factory(),
            'from_station_id' => Station::factory(),
            'to_station_id' => Station::factory(),
            'seat_number' => $this->faker->numberBetween(1, 12),
        ];
    }
} 