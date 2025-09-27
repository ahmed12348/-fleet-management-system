<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
    ];

    /**
     * Get all stocks for this warehouse
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get inventory items through stocks with quantity
     */
    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'stocks')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Get stock transfers from this warehouse
     */
    public function stockTransfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Get stock transfers to this warehouse
     */
    public function stockTransfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }
}
