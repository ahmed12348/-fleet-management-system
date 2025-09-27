<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{InventoryItem, User};
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_inventory_items_list()
    {
        // Create some inventory items
        InventoryItem::factory()->count(5)->create();

        $response = $this->getJson('/api/inventory-items');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data' => [
                             '*' => ['id', 'name', 'sku', 'price']
                         ]
                     ]
                 ]);
    }

    public function test_can_search_inventory_items_by_name()
    {
        InventoryItem::factory()->create(['name' => 'Widget A']);
        InventoryItem::factory()->create(['name' => 'Widget B']);
        InventoryItem::factory()->create(['name' => 'Gadget C']);

        $response = $this->getJson('/api/inventory-items?q=Widget');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data.data');
    }

    public function test_can_filter_inventory_items_by_price_range()
    {
        InventoryItem::factory()->create(['price' => 10.00]);
        InventoryItem::factory()->create(['price' => 50.00]);
        InventoryItem::factory()->create(['price' => 100.00]);

        $response = $this->getJson('/api/inventory-items?min_price=20&max_price=80');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.data');
    }

    public function test_authenticated_user_can_create_inventory_item()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $itemData = [
            'name' => 'Test Widget',
            'sku' => 'TW001',
            'price' => 25.99
        ];

        $response = $this->postJson('/api/inventory-items', $itemData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item created successfully'
                 ])
                 ->assertJsonFragment($itemData);

        $this->assertDatabaseHas('inventory_items', $itemData);
    }

    public function test_authenticated_user_can_update_inventory_item()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $item = InventoryItem::factory()->create(['name' => 'Original Name']);

        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/inventory-items/{$item->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item updated successfully'
                 ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_authenticated_user_can_delete_inventory_item()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $item = InventoryItem::factory()->create();

        $response = $this->deleteJson("/api/inventory-items/{$item->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Inventory item deleted successfully'
                 ]);

        $this->assertDatabaseMissing('inventory_items', ['id' => $item->id]);
    }

    public function test_unauthenticated_user_cannot_create_inventory_item()
    {
        $itemData = [
            'name' => 'Test Widget',
            'sku' => 'TW001',
            'price' => 25.99
        ];

        $response = $this->postJson('/api/inventory-items', $itemData);

        $response->assertStatus(401);
    }
}
