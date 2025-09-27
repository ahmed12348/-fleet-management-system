<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Warehouse, InventoryItem, Stock, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_warehouses_list()
    {
        Warehouse::factory()->count(3)->create();

        $response = $this->getJson('/api/warehouses');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_get_specific_warehouse()
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->getJson("/api/warehouses/{$warehouse->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $warehouse->id,
                         'name' => $warehouse->name,
                         'location' => $warehouse->location
                     ]
                 ]);
    }

    public function test_can_get_warehouse_inventory_with_caching()
    {
        $warehouse = Warehouse::factory()->create();
        $item1 = InventoryItem::factory()->create();
        $item2 = InventoryItem::factory()->create();

        // Create stocks
        Stock::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item1->id,
            'quantity' => 10
        ]);

        Stock::create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item2->id,
            'quantity' => 5
        ]);

        $response = $this->getJson("/api/warehouses/{$warehouse->id}/inventory");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'warehouse',
                         'inventory' => [
                             '*' => [
                                 'id',
                                 'inventory_item' => ['id', 'name', 'sku', 'price'],
                                 'quantity',
                                 'stock_level',
                                 'is_low_stock'
                             ]
                         ]
                     ]
                 ]);

        // Verify cache was created
        $this->assertTrue(Cache::has("warehouse_{$warehouse->id}_inventory"));
    }

    public function test_authenticated_user_can_create_warehouse()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $warehouseData = [
            'name' => 'Test Warehouse',
            'location' => 'Test City, Test State'
        ];

        $response = $this->postJson('/api/warehouses', $warehouseData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Warehouse created successfully'
                 ])
                 ->assertJsonFragment($warehouseData);

        $this->assertDatabaseHas('warehouses', $warehouseData);
    }

    public function test_warehouse_creation_clears_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create initial cache
        Cache::put('warehouses', 'cached_data', 300);

        $warehouseData = [
            'name' => 'Test Warehouse',
            'location' => 'Test City, Test State'
        ];

        $this->postJson('/api/warehouses', $warehouseData);

        // Verify cache was cleared
        $this->assertFalse(Cache::has('warehouses'));
    }

    public function test_unauthenticated_user_cannot_create_warehouse()
    {
        $warehouseData = [
            'name' => 'Test Warehouse',
            'location' => 'Test City, Test State'
        ];

        $response = $this->postJson('/api/warehouses', $warehouseData);

        $response->assertStatus(401);
    }
}
