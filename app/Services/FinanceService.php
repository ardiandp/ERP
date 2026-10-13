<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Payment;
use Illuminate\Support\Collection;

class FinanceService
{
    public function accountsWithBalances(): Collection
    {
        $accounts = FinancialAccount::orderBy('account_code')->get();

        $payments = Payment::query()
            ->selectRaw('financial_account_id, SUM(amount) as total')
            ->whereNotNull('financial_account_id')
            ->groupBy('financial_account_id')
            ->pluck('total', 'financial_account_id');

        $expenses = Expense::query()
            ->selectRaw('financial_account_id, SUM(amount) as total')
            ->whereIn('status', ['APPROVED', 'PAID'])
            ->whereNotNull('financial_account_id')
            ->groupBy('financial_account_id')
            ->pluck('total', 'financial_account_id');

        $bankIn = BankTransaction::query()
            ->selectRaw('financial_account_id, SUM(amount) as total')
            ->where('transaction_type', 'IN')
            ->groupBy('financial_account_id')
            ->pluck('total', 'financial_account_id');

        $bankOut = BankTransaction::query()
            ->selectRaw('financial_account_id, SUM(amount) as total')
            ->where('transaction_type', 'OUT')
            ->groupBy('financial_account_id')
            ->pluck('total', 'financial_account_id');

        return $accounts->map(function (FinancialAccount $account) use ($payments, $expenses, $bankIn, $bankOut) {
            $opening = (float) $account->opening_balance;
            $in = (float) ($payments[$account->id] ?? 0) + (float) ($bankIn[$account->id] ?? 0);
            $out = (float) ($expenses[$account->id] ?? 0) + (float) ($bankOut[$account->id] ?? 0);

            $account->setAttribute('computed_balance', $opening + $in - $out);
            $account->setAttribute('total_in', $in);
            $account->setAttribute('total_out', $out);

            return $account;
        });
    }
}
