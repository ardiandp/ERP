<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $fillable = ['sales_return_id', 'sales_transaction_item_id', 'product_id', 'quantity', 'amount'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
