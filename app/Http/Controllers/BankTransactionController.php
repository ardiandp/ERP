<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = BankTransaction::with('financialAccount')
            ->when($request->filled('financial_account_id'), fn ($query) => $query->where('financial_account_id', $request->input('financial_account_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('reconciliation_status', $request->input('status')))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('bank-transactions.index', [
            'transactions' => $transactions,
            'accounts' => FinancialAccount::orderBy('account_name')->get(),
        ]);
    }

    public function create()
    {
        return view('bank-transactions.create', [
            'accounts' => FinancialAccount::orderBy('account_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'financial_account_id' => ['required', 'exists:financial_accounts,id'],
            'transaction_date' => ['required', 'date'],
            'transaction_type' => ['required', 'in:IN,OUT'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
            'balance' => ['nullable', 'numeric'],
            'external_reference' => ['nullable', 'string'],
        ]);

        BankTransaction::create(array_merge($data, ['reconciliation_status' => 'UNRECONCILED']));

        return redirect()->route('bank-transactions.index')->with('success', 'Mutasi bank dicatat.');
    }
}
