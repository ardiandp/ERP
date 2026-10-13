<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $fillable = ['account_code', 'account_type', 'bank_id', 'branch_id', 'account_number', 'account_name', 'currency', 'opening_balance', 'status'];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
