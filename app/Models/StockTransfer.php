<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = ['transfer_number', 'from_warehouse_id', 'to_warehouse_id', 'transfer_date', 'status', 'requested_by', 'approved_by', 'approved_at', 'notes'];

    protected $casts = ['transfer_date' => 'date', 'approved_at' => 'datetime'];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
