<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTransactionItem extends Model
{
    protected $fillable = ['sales_transaction_id', 'item_type', 'product_id', 'service_id', 'package_id', 'item_name', 'quantity', 'unit_price', 'discount', 'subtotal', 'employee_id', 'source_booking_item_id', 'notes'];

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
