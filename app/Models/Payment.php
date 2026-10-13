<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['payment_number', 'transaction_type', 'transaction_id', 'payment_method_id', 'financial_account_id', 'amount', 'payment_date', 'reference_number', 'status', 'notes', 'created_by'];

    protected $casts = ['payment_date' => 'date'];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function transaction()
    {
        return $this->morphTo(__FUNCTION__, 'transaction_type', 'transaction_id');
    }

    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class);
    }
}
