<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number', 'supplier_id', 'branch_id', 'order_date', 'expected_date', 'status', 'subtotal', 'discount', 'tax', 'grand_total', 'notes', 'created_by', 'approved_by', 'approved_at'];

    protected $casts = ['order_date' => 'date', 'expected_date' => 'date', 'approved_at' => 'datetime'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
