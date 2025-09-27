<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'inventory_item_id',
        'quantity',
        'status',
        'performed_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the source warehouse
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the inventory item being transferred
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the user who performed the transfer
     */
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope for completed transfers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}


