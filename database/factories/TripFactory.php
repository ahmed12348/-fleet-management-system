<?php

// database/factories/TripFactory.php
namespace Database\Factories;

use App\Models\Trip;
use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        return [
            'name' => 'Test Trip',
            'bus_id' => Bus::factory(),
        ];
    }
}
