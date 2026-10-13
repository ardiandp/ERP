<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    protected $fillable = ['invoice_number', 'branch_id', 'customer_id', 'cashier_id', 'transaction_date', 'subtotal', 'discount', 'tax', 'grand_total', 'status', 'notes', 'created_by'];

    protected $casts = ['transaction_date' => 'date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id')->where('transaction_type', 'SALE');
    }
}
