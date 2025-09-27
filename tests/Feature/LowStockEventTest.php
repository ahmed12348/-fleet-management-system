<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Warehouse, InventoryItem, Stock, User};
use App\Events\LowStockDetected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class LowStockEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_event_dispatched_when_stock_falls_below_threshold()
    {
        Event::fake();

        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();
        $user = User::factory()->create();

        // Create stock with quantity that will fall below threshold after transfer
        Stock::create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 6, // Will be 4 after transfer (below threshold of 5)
        ]);

        $this->actingAs($user, 'sanctum');

        $payload = [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 2,
        ];

        $response = $this->postJson('/api/stock-transfers', $payload);
        
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        // Verify the event was dispatched
        Event::assertDispatched(LowStockDetected::class, function ($event) use ($from, $item) {
            return $event->stock->warehouse_id === $from->id 
                && $event->stock->inventory_item_id === $item->id
                && $event->stock->quantity < 5;
        });
    }

    public function test_low_stock_event_not_dispatched_when_stock_above_threshold()
    {
        Event::fake();

        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();
        $user = User::factory()->create();

        // Create stock with quantity that will remain above threshold after transfer
        Stock::create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 20, // Will be 15 after transfer (above threshold of 5)
        ]);

        $this->actingAs($user, 'sanctum');

        $payload = [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5,
        ];

        $response = $this->postJson('/api/stock-transfers', $payload);
        
        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        // Verify the event was NOT dispatched
        Event::assertNotDispatched(LowStockDetected::class);
    }

    public function test_low_stock_event_dispatched_with_correct_stock_data()
    {
        Event::fake();

        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();
        $user = User::factory()->create();

        Stock::create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 7,
        ]);

        $this->actingAs($user, 'sanctum');

        $payload = [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 3, // Will result in quantity of 4 (below threshold)
        ];

        $this->postJson('/api/stock-transfers', $payload);

        Event::assertDispatched(LowStockDetected::class, function ($event) use ($from, $item) {
            return $event->stock->warehouse_id === $from->id 
                && $event->stock->inventory_item_id === $item->id
                && $event->stock->quantity === 4
                && $event->stock->isLowStock();
        });
    }
}
