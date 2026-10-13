<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillTemplate extends Model
{
    protected $fillable = ['code', 'name', 'category', 'frequency', 'default_amount', 'default_due_day', 'branch_id', 'supplier_id', 'financial_account_id', 'status', 'notes'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
