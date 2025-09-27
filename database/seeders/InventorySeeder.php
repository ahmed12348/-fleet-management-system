<?php

namespace Database\Seeders;

use App\Models\{User, Warehouse, InventoryItem, Stock, StockTransfer};
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users
        $users = User::factory()->count(3)->create();

        // Create warehouses
        $warehouses = collect([
            ['name' => 'Main Warehouse', 'location' => 'New York, NY'],
            ['name' => 'Secondary Warehouse', 'location' => 'Los Angeles, CA'],
            ['name' => 'Distribution Center', 'location' => 'Chicago, IL'],
            ['name' => 'Regional Hub', 'location' => 'Miami, FL'],
        ])->map(function ($warehouse) {
            return Warehouse::create($warehouse);
        });

        // Create inventory items
        $inventoryItems = collect([
            ['name' => 'Widget Pro', 'sku' => 'WP001', 'price' => 29.99],
            ['name' => 'Gadget Max', 'sku' => 'GM001', 'price' => 49.99],
            ['name' => 'Tool Kit Basic', 'sku' => 'TKB001', 'price' => 19.99],
            ['name' => 'Component A', 'sku' => 'CA001', 'price' => 15.50],
            ['name' => 'Component B', 'sku' => 'CB001', 'price' => 25.75],
            ['name' => 'Accessory Pack', 'sku' => 'AP001', 'price' => 12.99],
            ['name' => 'Premium Widget', 'sku' => 'PW001', 'price' => 89.99],
            ['name' => 'Standard Cable', 'sku' => 'SC001', 'price' => 8.99],
        ])->map(function ($item) {
            return InventoryItem::create($item);
        });

        // Create stocks with varying quantities
        $warehouses->each(function ($warehouse, $warehouseIndex) use ($inventoryItems) {
            $inventoryItems->each(function ($item, $itemIndex) use ($warehouse, $warehouseIndex) {
                // Create different stock levels for variety
                $baseQuantity = rand(0, 100);
                $quantity = $baseQuantity + ($warehouseIndex * 10) + ($itemIndex * 5);
                
                // Some items have low stock for testing
                if ($itemIndex % 3 === 0) {
                    $quantity = rand(1, 4);
                }

                Stock::create([
                    'warehouse_id' => $warehouse->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => $quantity,
                ]);
            });
        });

        // Create some stock transfers
        $stockTransfers = collect([
            [
                'from_warehouse_id' => $warehouses[0]->id,
                'to_warehouse_id' => $warehouses[1]->id,
                'inventory_item_id' => $inventoryItems[0]->id,
                'quantity' => 25,
                'status' => 'completed',
                'performed_by' => $users[0]->id,
            ],
            [
                'from_warehouse_id' => $warehouses[1]->id,
                'to_warehouse_id' => $warehouses[2]->id,
                'inventory_item_id' => $inventoryItems[1]->id,
                'quantity' => 15,
                'status' => 'completed',
                'performed_by' => $users[1]->id,
            ],
            [
                'from_warehouse_id' => $warehouses[2]->id,
                'to_warehouse_id' => $warehouses[3]->id,
                'inventory_item_id' => $inventoryItems[2]->id,
                'quantity' => 30,
                'status' => 'pending',
                'performed_by' => $users[2]->id,
            ],
        ])->map(function ($transfer) {
            return StockTransfer::create($transfer);
        });

        $this->command->info('Inventory data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . $users->count() . ' users');
        $this->command->info('- ' . $warehouses->count() . ' warehouses');
        $this->command->info('- ' . $inventoryItems->count() . ' inventory items');
        $this->command->info('- ' . Stock::count() . ' stock records');
        $this->command->info('- ' . $stockTransfers->count() . ' stock transfers');
    }
}
