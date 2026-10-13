<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SalesTransaction;
use App\Support\DocumentNumber;

class PaymentService
{
    public function record(SalesTransaction $transaction, array $data): Payment
    {
        return Payment::create([
            'payment_number' => DocumentNumber::generate('PAY', Payment::class, 'payment_number', $data['payment_date'] ?? null),
            'transaction_type' => 'SALE',
            'transaction_id' => $transaction->id,
            'payment_method_id' => $data['payment_method_id'],
            'financial_account_id' => $data['financial_account_id'] ?? null,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'reference_number' => $data['reference_number'] ?? null,
            'status' => 'POSTED',
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
