<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Warehouse, InventoryItem, Stock, User, StockTransfer};
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_transfer_updates_db()
    {
        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        Stock::create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 20,
        ]);

        $payload = [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ];

        $response = $this->postJson('/api/stock-transfers', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Stock transfer completed successfully.'
                 ])
                 ->assertJsonFragment(['status' => 'completed']);

        // Verify source stock was reduced
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        // Verify destination stock was created/increased
        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        // Verify transfer record was created
        $this->assertDatabaseHas('stock_transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
            'status' => 'completed',
            'performed_by' => $user->id,
        ]);
    }

    public function test_can_get_stock_transfers_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create some transfers
        StockTransfer::factory()->count(3)->create();

        $response = $this->getJson('/api/stock-transfers');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'from_warehouse_id',
                                 'to_warehouse_id',
                                 'inventory_item_id',
                                 'quantity',
                                 'status',
                                 'performed_by'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_can_get_specific_stock_transfer()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $transfer = StockTransfer::factory()->create();

        $response = $this->getJson("/api/stock-transfers/{$transfer->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $transfer->id
                     ]
                 ]);
    }
}
