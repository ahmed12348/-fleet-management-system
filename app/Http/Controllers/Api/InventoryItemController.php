<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class InventoryItemController extends Controller
{
    /**
     * Get inventory items with search and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = InventoryItem::query();

        // Search by name
        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search by SKU
        if ($request->has('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }

        // Order by
        $orderBy = $request->get('order_by', 'name');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = $request->get('per_page', 10);
        $items = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Create a new inventory item
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:inventory_items,sku',
            'price' => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item created successfully',
            'data' => $item
        ], 201);
    }

    /**
     * Get a specific inventory item
     */
    public function show($id): JsonResponse
    {
        $item = InventoryItem::with(['stocks.warehouse', 'stockTransfers'])
                            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    /**
     * Update an inventory item
     */
    public function update(Request $request, $id): JsonResponse
    {
        $item = InventoryItem::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|max:50|unique:inventory_items,sku,' . $id,
            'price' => 'sometimes|numeric|min:0',
        ]);

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item updated successfully',
            'data' => $item
        ]);
    }

    /**
     * Delete an inventory item
     */
    public function destroy($id): JsonResponse
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully'
        ]);
    }
}
