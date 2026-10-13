<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'name', 'method_type', 'financial_account_id', 'status'];

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}
