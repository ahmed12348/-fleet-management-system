<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    /**
     * Get all warehouses
     */
    public function index(): JsonResponse
    {
        $warehouses = Cache::remember('warehouses', 300, function () {
            return Warehouse::with('stocks.inventoryItem')->get();
        });

        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }

    /**
     * Create a new warehouse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        $warehouse = Warehouse::create($data);
        
        // Clear cache
        Cache::forget('warehouses');

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouse
        ], 201);
    }

    /**
     * Get a specific warehouse
     */
    public function show($id): JsonResponse
    {
        $warehouse = Warehouse::with('stocks.inventoryItem')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $warehouse
        ]);
    }

    /**
     * Get inventory for a specific warehouse with caching
     */
    public function inventory($id): JsonResponse
    {
        $cacheKey = "warehouse_{$id}_inventory";
        
        $inventory = Cache::remember($cacheKey, 300, function () use ($id) {
            $warehouse = Warehouse::with(['stocks.inventoryItem'])->findOrFail($id);
            
            return $warehouse->stocks->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'inventory_item' => [
                        'id' => $stock->inventoryItem->id,
                        'name' => $stock->inventoryItem->name,
                        'sku' => $stock->inventoryItem->sku,
                        'price' => $stock->inventoryItem->price,
                    ],
                    'quantity' => $stock->quantity,
                    'stock_level' => $stock->stock_level,
                    'is_low_stock' => $stock->isLowStock(),
                ];
            });
        });

        return response()->json([
            'success' => true,
            'data' => [
                'warehouse' => Warehouse::findOrFail($id),
                'inventory' => $inventory
            ]
        ]);
    }
}
