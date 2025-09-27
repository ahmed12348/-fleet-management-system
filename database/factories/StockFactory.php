<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Create stock with low quantity
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(0, 4),
        ]);
    }

    /**
     * Create stock with high quantity
     */
    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(50, 200),
        ]);
    }
}
