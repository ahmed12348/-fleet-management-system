<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'inventory_item_id',
        'quantity',
    ];

    /**
     * Get the warehouse that owns this stock
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the inventory item for this stock
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Check if stock is low (below threshold)
     */
    public function isLowStock($threshold = 5)
    {
        return $this->quantity < $threshold;
    }

    /**
     * Get stock level status
     */
    public function getStockLevelAttribute()
    {
        if ($this->quantity == 0) {
            return 'out_of_stock';
        } elseif ($this->quantity < 5) {
            return 'low_stock';
        } elseif ($this->quantity < 20) {
            return 'medium_stock';
        } else {
            return 'high_stock';
        }
    }
}
