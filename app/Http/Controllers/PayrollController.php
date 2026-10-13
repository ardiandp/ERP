<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\PayslipTemplate;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrolls) {}

    public function index(Request $request)
    {
        $payrolls = Payroll::with('employee', 'period')
            ->when($request->filled('payroll_period_id'), fn ($query) => $query->where('payroll_period_id', $request->input('payroll_period_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('payrolls.index', [
            'payrolls' => $payrolls,
            'periods' => PayrollPeriod::latest('period_start')->get(),
        ]);
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee', 'period', 'items', 'adjustments', 'payslip');

        return view('payrolls.show', [
            'payroll' => $payroll,
            'templates' => PayslipTemplate::where('status', 'ACTIVE')->get(),
            'methods' => PaymentMethod::where('status', 'ACTIVE')->get(),
            'accounts' => FinancialAccount::orderBy('account_name')->get(),
        ]);
    }

    public function generate(Request $request, PayrollPeriod $payroll_period)
    {
        try {
            $created = $this->payrolls->generate($payroll_period);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['generate' => $exception->getMessage()]);
        }

        $count = count($created);

        return redirect()->route('payroll-periods.show', $payroll_period)
            ->with('success', $count > 0 ? "{$count} payroll dibuat." : 'Payroll sudah pernah dibuat untuk periode ini.');
    }

    public function adjust(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'type' => ['required', 'in:EARNING,DEDUCTION'],
            'description' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $this->payrolls->adjust($payroll, $data);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['adjust' => $exception->getMessage()]);
        }

        return back()->with('success', 'Adjustment ditambahkan.');
    }

    public function approve(Payroll $payroll)
    {
        try {
            $this->payrolls->approve($payroll);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Payroll disetujui.');
    }

    public function payslip(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'exists:payslip_templates,id'],
        ]);

        try {
            $this->payrolls->generatePayslip($payroll, $data['template_id'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['payslip' => $exception->getMessage()]);
        }

        return back()->with('success', 'Slip gaji dibuat.');
    }

    public function pay(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'financial_account_id' => ['nullable', 'exists:financial_accounts,id'],
            'payment_date' => ['nullable', 'date'],
        ]);

        try {
            $this->payrolls->pay($payroll, $data);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['pay' => $exception->getMessage()]);
        }

        return back()->with('success', 'Payroll dibayar.');
    }
}
