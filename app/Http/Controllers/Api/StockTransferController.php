<?php

namespace App\Http\Controllers\Api;

use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class StockTransferController extends Controller
{
    /**
     * Get all stock transfers
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'performedBy']);

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                  ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by inventory item
        if ($request->has('inventory_item_id')) {
            $query->where('inventory_item_id', $request->inventory_item_id);
        }

        $perPage = $request->get('per_page', 15);
        $transfers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transfers
        ]);
    }

    /**
     * Create a new stock transfer
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Verify warehouses and inventory item exist
        $fromWarehouse = Warehouse::findOrFail($data['from_warehouse_id']);
        $toWarehouse = Warehouse::findOrFail($data['to_warehouse_id']);
        $inventoryItem = InventoryItem::findOrFail($data['inventory_item_id']);

        // Use database transaction for data consistency
        return DB::transaction(function () use ($data, $request) {
            // Find stock in source warehouse
            $stockFrom = Stock::where('warehouse_id', $data['from_warehouse_id'])
                              ->where('inventory_item_id', $data['inventory_item_id'])
                              ->first();

            if (!$stockFrom || $stockFrom->quantity < $data['quantity']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Not enough stock available for transfer.'
                ], 422);
            }

            // Deduct from source
            $stockFrom->decrement('quantity', $data['quantity']);

            // Add to destination
            $stockTo = Stock::firstOrCreate(
                [
                    'warehouse_id' => $data['to_warehouse_id'],
                    'inventory_item_id' => $data['inventory_item_id'],
                ],
                ['quantity' => 0]
            );
            $stockTo->increment('quantity', $data['quantity']);

            // Log transfer
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'inventory_item_id' => $data['inventory_item_id'],
                'quantity' => $data['quantity'],
                'status' => 'completed',
                'performed_by' => $request->user()->id,
            ]);

            // Clear related caches
            Cache::forget('warehouses');
            Cache::forget("warehouse_{$data['from_warehouse_id']}_inventory");
            Cache::forget("warehouse_{$data['to_warehouse_id']}_inventory");

            // Trigger LowStock event if needed
            if ($stockFrom->fresh()->isLowStock()) {
                event(new LowStockDetected($stockFrom->fresh()));
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer completed successfully.',
                'data' => $transfer->load(['fromWarehouse', 'toWarehouse', 'inventoryItem'])
            ], 201);
        });
    }

    /**
     * Get a specific stock transfer
     */
    public function show($id): JsonResponse
    {
        $transfer = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'performedBy'])
                                ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transfer
        ]);
    }

    /**
     * Cancel a pending transfer
     */
    public function cancel($id): JsonResponse
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'Only pending transfers can be cancelled.'
            ], 422);
        }

        $transfer->update(['status' => 'failed']);

        return response()->json([
            'success' => true,
            'message' => 'Transfer cancelled successfully.',
            'data' => $transfer
        ]);
    }
}
