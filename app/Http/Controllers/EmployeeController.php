<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('employees.index', ['employees' => Employee::with(['branch', 'department', 'position'])->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('employees.create', [
            'branches' => Branch::all(),
            'departments' => Department::all(),
            'positions' => Position::all(),
            'managers' => Employee::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $employee = Employee::create($data);
        AuditLog::record('create', $employee, [], $data);

        return redirect()->route('employees.index')->with('success', 'Employee dibuat.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['branch', 'department', 'position', 'manager', 'familyMembers', 'insurances']);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', [
            'employee' => $employee,
            'branches' => Branch::all(),
            'departments' => Department::all(),
            'positions' => Position::all(),
            'managers' => Employee::all(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validateData($request, $employee);
        $employee->update($data);
        AuditLog::record('update', $employee, [], $data);

        return redirect()->route('employees.index')->with('success', 'Employee diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        AuditLog::record('delete', $employee, $employee->toArray());
        $employee->delete();

        return back()->with('success', 'Employee dihapus.');
    }

    protected function validateData(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'employee_code' => ['required', 'unique:employees,employee_code,'.($employee?->id)],
            'full_name' => ['required', 'string'],
            'nik' => ['nullable', 'string'],
            'birth_place' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:M,F'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'marital_status' => ['nullable', 'string'],
            'employment_status' => ['required', 'string'],
            'join_date' => ['nullable', 'date'],
            'resign_date' => ['nullable', 'date'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
