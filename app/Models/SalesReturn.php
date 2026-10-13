<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = ['return_number', 'sales_transaction_id', 'branch_id', 'return_date', 'reason', 'status', 'created_by'];

    protected $casts = ['return_date' => 'date'];

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }
}
