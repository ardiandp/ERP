<?php

namespace App\Http\Controllers;

use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\Payment;
use App\Services\BankReconciliationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BankReconciliationController extends Controller
{
    public function __construct(protected BankReconciliationService $reconciliations) {}

    public function index()
    {
        return view('bank-reconciliations.index', [
            'reconciliations' => BankReconciliation::with('bankTransaction.financialAccount', 'payment')->latest('id')->paginate(20),
        ]);
    }

    public function create(Request $request)
    {
        $bankTransaction = $request->filled('bank_transaction')
            ? BankTransaction::find($request->input('bank_transaction'))
            : null;

        return view('bank-reconciliations.create', [
            'bankTransaction' => $bankTransaction,
            'bankTransactions' => BankTransaction::where('reconciliation_status', 'UNRECONCILED')->with('financialAccount')->orderByDesc('transaction_date')->get(),
            'payments' => Payment::latest('id')->take(100)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_transaction_id' => ['required', 'exists:bank_transactions,id'],
            'payment_id' => ['required', 'exists:payments,id'],
            'matched_amount' => ['nullable', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $bankTransaction = BankTransaction::findOrFail($data['bank_transaction_id']);

        try {
            $this->reconciliations->match($bankTransaction, $data);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['reconciliation' => $exception->getMessage()]);
        }

        return redirect()->route('bank-reconciliations.index')->with('success', 'Mutasi bank berhasil direkonsiliasi.');
    }
}
