<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function store(Request $request, SalesTransaction $sale)
    {
        $data = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'financial_account_id' => ['nullable', 'exists:financial_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->payments->record($sale, $data);

        return redirect()->route('sales.show', $sale)->with('success', 'Pembayaran dicatat.');
    }
}
