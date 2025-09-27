<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\{Warehouse, InventoryItem, Stock, User};
use App\Http\Controllers\Api\StockTransferController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class StockTransferValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_over_transfer_fails_validation()
    {
        // Create test data
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $inventoryItem = InventoryItem::factory()->create();
        $user = User::factory()->create();

        // Create stock with limited quantity
        $stock = Stock::create([
            'warehouse_id' => $fromWarehouse->id,
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 10,
        ]);

        $this->actingAs($user, 'sanctum');

        // Attempt to transfer more than available
        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 15, // More than available (10)
        ]);

        // Should fail with 422 status
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => 'Not enough stock available for transfer.'
                 ]);

        // Verify stock quantity hasn't changed
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $fromWarehouse->id,
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 10,
        ]);

        // Verify no transfer was created
        $this->assertDatabaseMissing('stock_transfers', [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => $inventoryItem->id,
        ]);
    }

    public function test_transfer_with_zero_quantity_fails()
    {
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $inventoryItem = InventoryItem::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantity']);
    }

    public function test_transfer_to_same_warehouse_fails()
    {
        $warehouse = Warehouse::factory()->create();
        $inventoryItem = InventoryItem::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $warehouse->id,
            'to_warehouse_id' => $warehouse->id, // Same warehouse
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['to_warehouse_id']);
    }

    public function test_transfer_with_nonexistent_warehouse_fails()
    {
        $inventoryItem = InventoryItem::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => 999, // Non-existent warehouse
            'to_warehouse_id' => 998,   // Non-existent warehouse
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['from_warehouse_id', 'to_warehouse_id']);
    }
}
