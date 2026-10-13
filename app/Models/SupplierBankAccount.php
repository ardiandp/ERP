<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBankAccount extends Model
{
    protected $fillable = ['supplier_id', 'bank_id', 'account_number', 'account_name', 'branch_name', 'is_primary', 'status'];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
