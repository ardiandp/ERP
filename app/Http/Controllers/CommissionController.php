<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCommission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = EmployeeCommission::with(['employee', 'transaction'])
            ->when($request->filled('employee_id'), fn ($query) => $query->where('employee_id', $request->input('employee_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('commissions.index', [
            'commissions' => $commissions,
            'employees' => Employee::orderBy('full_name')->get(),
            'total' => EmployeeCommission::when($request->filled('employee_id'), fn ($query) => $query->where('employee_id', $request->input('employee_id')))->sum('commission_amount'),
        ]);
    }
}
