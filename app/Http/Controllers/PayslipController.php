<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $payslips = Payslip::with('employee', 'payroll.period')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('payslips.index', ['payslips' => $payslips]);
    }

    public function show(Payslip $payslip)
    {
        $payslip->load('employee', 'payroll.period', 'items');

        return view('payslips.show', ['payslip' => $payslip]);
    }

    public function publish(Payslip $payslip)
    {
        if ($payslip->status === 'PUBLISHED') {
            return back()->withErrors(['status' => 'Slip sudah dipublikasikan.']);
        }

        $payslip->update(['status' => 'PUBLISHED', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Slip gaji dipublikasikan.');
    }
}
