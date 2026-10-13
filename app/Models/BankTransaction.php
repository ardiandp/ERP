<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $fillable = ['financial_account_id', 'transaction_date', 'transaction_type', 'amount', 'description', 'reference_number', 'balance', 'external_reference', 'reconciliation_status', 'imported_at'];

    protected $casts = ['transaction_date' => 'date', 'imported_at' => 'datetime'];

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class);
    }
}
