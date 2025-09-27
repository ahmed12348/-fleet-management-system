<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'price',
    ];

    /**
     * Get all stocks for this inventory item
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get warehouses through stocks with quantity
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'stocks')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Get stock transfers for this item
     */
    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class);
    }

    /**
     * Get total quantity across all warehouses
     */
    public function getTotalQuantityAttribute()
    {
        return $this->stocks()->sum('quantity');
    }
}
