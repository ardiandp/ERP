<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    protected $fillable = ['bank_transaction_id', 'payment_id', 'matched_amount', 'status', 'reconciled_by', 'reconciled_at', 'notes'];

    protected $casts = ['reconciled_at' => 'datetime'];

    public function bankTransaction()
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
