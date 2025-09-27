<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Warehouse, InventoryItem, Stock, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_stocks_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Stock::factory()->count(3)->create();

        $response = $this->getJson('/api/stocks');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data' => [
                             '*' => ['id', 'warehouse_id', 'inventory_item_id', 'quantity']
                         ]
                     ]
                 ]);
    }

    public function test_can_filter_stocks_by_warehouse()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::create([
            'warehouse_id' => $warehouse1->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10
        ]);

        Stock::create([
            'warehouse_id' => $warehouse2->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5
        ]);

        $response = $this->getJson("/api/stocks?warehouse_id={$warehouse1->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data');
    }

    public function test_can_filter_low_stock_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $warehouse = Warehouse::factory()->create();
        $item1 = InventoryItem::factory()->create();
        $item2 = InventoryItem::factory()->create();

        // Low stock
        Stock::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item1->id,
            'quantity' => 3
        ]);

        // Normal stock
        Stock::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item2->id,
            'quantity' => 20
        ]);

        $response = $this->getJson('/api/stocks?low_stock=1');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data');
    }

    public function test_authenticated_user_can_create_stock()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $warehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        $stockData = [
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 25
        ];

        $response = $this->postJson('/api/stocks', $stockData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Stock updated successfully'
                 ]);

        $this->assertDatabaseHas('stocks', $stockData);
    }

    public function test_authenticated_user_can_update_stock()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $stock = Stock::factory()->create(['quantity' => 10]);

        $updateData = ['quantity' => 15];

        $response = $this->putJson("/api/stocks/{$stock->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Stock updated successfully'
                 ]);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 15
        ]);
    }

    public function test_authenticated_user_can_delete_stock()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $stock = Stock::factory()->create();

        $response = $this->deleteJson("/api/stocks/{$stock->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Stock deleted successfully'
                 ]);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_stock_operations_clear_related_caches()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $warehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        // Create initial caches
        Cache::put('warehouses', 'cached_data', 300);
        Cache::put("warehouse_{$warehouse->id}_inventory", 'cached_data', 300);

        $stockData = [
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 25
        ];

        $this->postJson('/api/stocks', $stockData);

        // Verify caches were cleared
        $this->assertFalse(Cache::has('warehouses'));
        $this->assertFalse(Cache::has("warehouse_{$warehouse->id}_inventory"));
    }

    public function test_unauthenticated_user_cannot_manage_stocks()
    {
        $warehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        $stockData = [
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 25
        ];

        $response = $this->postJson('/api/stocks', $stockData);
        $response->assertStatus(401);

        $response = $this->getJson('/api/stocks');
        $response->assertStatus(401);
    }
}
