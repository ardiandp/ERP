<?php

namespace App\Services;

use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BankReconciliationService
{
    public function match(BankTransaction $bankTransaction, array $data): BankReconciliation
    {
        $payment = ! empty($data['payment_id']) ? Payment::find($data['payment_id']) : null;

        if (! $payment) {
            throw new InvalidArgumentException('Transaksi pembayaran tidak ditemukan.');
        }

        if ($bankTransaction->reconciliation_status === 'RECONCILED') {
            throw new InvalidArgumentException('Mutasi bank sudah direkonsiliasi.');
        }

        return DB::transaction(function () use ($bankTransaction, $payment, $data) {
            $reconciliation = BankReconciliation::create([
                'bank_transaction_id' => $bankTransaction->id,
                'payment_id' => $payment->id,
                'matched_amount' => $data['matched_amount'] ?? $bankTransaction->amount,
                'status' => 'MATCHED',
                'reconciled_by' => auth()->id(),
                'reconciled_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $bankTransaction->update(['reconciliation_status' => 'RECONCILED']);

            return $reconciliation;
        });
    }
}
