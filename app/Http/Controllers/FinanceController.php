<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;

class FinanceController extends Controller
{
    public function __construct(protected FinanceService $finance) {}

    public function accounts()
    {
        return view('finance.accounts', [
            'accounts' => $this->finance->accountsWithBalances(),
        ]);
    }
}
