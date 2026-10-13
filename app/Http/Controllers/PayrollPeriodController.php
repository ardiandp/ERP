<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PayrollPeriodController extends Controller
{
    public function __construct(protected PayrollService $payrolls) {}

    public function index()
    {
        return view('payroll-periods.index', [
            'periods' => PayrollPeriod::withCount('payrolls')->latest('period_start')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('payroll-periods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'unique:payroll_periods,code'],
            'name' => ['required', 'string'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'payment_date' => ['nullable', 'date'],
        ]);

        PayrollPeriod::create(array_merge($data, ['status' => 'OPEN']));

        return redirect()->route('payroll-periods.index')->with('success', 'Periode payroll dibuat.');
    }

    public function show(PayrollPeriod $payroll_period)
    {
        $payroll_period->load(['payrolls.employee']);

        return view('payroll-periods.show', ['period' => $payroll_period]);
    }

    public function close(PayrollPeriod $payroll_period)
    {
        try {
            $this->payrolls->closePeriod($payroll_period);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Periode payroll ditutup.');
    }
}
