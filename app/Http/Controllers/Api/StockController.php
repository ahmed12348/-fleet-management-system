<?php

namespace App\Http\Controllers\Api;

use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    /**
     * Get all stocks with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Stock::with(['warehouse', 'inventoryItem']);

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by inventory item
        if ($request->has('inventory_item_id')) {
            $query->where('inventory_item_id', $request->inventory_item_id);
        }

        // Filter by low stock
        if ($request->has('low_stock') && $request->low_stock) {
            $query->where('quantity', '<', 5);
        }

        $perPage = $request->get('per_page', 15);
        $stocks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $stocks
        ]);
    }

    /**
     * Add or update stock
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:0',
        ]);

        // Verify warehouse and inventory item exist
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $inventoryItem = InventoryItem::findOrFail($data['inventory_item_id']);

        $stock = Stock::updateOrCreate(
            [
                'warehouse_id' => $data['warehouse_id'],
                'inventory_item_id' => $data['inventory_item_id']
            ],
            ['quantity' => $data['quantity']]
        );

        // Clear related caches
        Cache::forget('warehouses');
        Cache::forget("warehouse_{$data['warehouse_id']}_inventory");

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => $stock->load(['warehouse', 'inventoryItem'])
        ], 201);
    }

    /**
     * Update stock quantity
     */
    public function update(Request $request, $id): JsonResponse
    {
        $stock = Stock::findOrFail($id);

        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $stock->update($data);

        // Clear related caches
        Cache::forget('warehouses');
        Cache::forget("warehouse_{$stock->warehouse_id}_inventory");

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => $stock->load(['warehouse', 'inventoryItem'])
        ]);
    }

    /**
     * Get a specific stock
     */
    public function show($id): JsonResponse
    {
        $stock = Stock::with(['warehouse', 'inventoryItem'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $stock
        ]);
    }

    /**
     * Delete stock
     */
    public function destroy($id): JsonResponse
    {
        $stock = Stock::findOrFail($id);
        $warehouseId = $stock->warehouse_id;
        
        $stock->delete();

        // Clear related caches
        Cache::forget('warehouses');
        Cache::forget("warehouse_{$warehouseId}_inventory");

        return response()->json([
            'success' => true,
            'message' => 'Stock deleted successfully'
        ]);
    }
}
