<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransfer>
 */
class StockTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        
        return [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => $this->faker->numberBetween(1, 50),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'performed_by' => User::factory(),
        ];
    }

    /**
     * Create a completed transfer
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Create a pending transfer
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
