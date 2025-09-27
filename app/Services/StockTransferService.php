<?php 

namespace App\Services;

use App\Models\{Stock, StockTransfer};
use Illuminate\Support\Facades\DB;
use App\Events\LowStockDetected;

class StockTransferService {
    public function transfer($fromId, $toId, $itemId, $qty, $userId) {
        return DB::transaction(function () use ($fromId,$toId,$itemId,$qty,$userId) {
            $fromStock = Stock::where('warehouse_id',$fromId)
                              ->where('inventory_item_id',$itemId)
                              ->lockForUpdate()
                              ->firstOrFail();

            if ($fromStock->quantity < $qty) {
                throw new \Exception("Insufficient stock");
            }

            $fromStock->decrement('quantity', $qty);

            $toStock = Stock::firstOrCreate(
                ['warehouse_id'=>$toId, 'inventory_item_id'=>$itemId],
                ['quantity'=>0]
            );
            $toStock->increment('quantity', $qty);

            $transfer = StockTransfer::create([
                'from_warehouse_id'=>$fromId,
                'to_warehouse_id'=>$toId,
                'inventory_item_id'=>$itemId,
                'quantity'=>$qty,
                'status'=>'completed',
                'performed_by'=>$userId,
            ]);

            if ($fromStock->quantity < 5) {
                event(new LowStockDetected($itemId, $fromId, $fromStock->quantity));
            }

            return $transfer;
        });
    }
}
