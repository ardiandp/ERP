<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        return view('shifts.index', [
            'shifts' => Shift::with('branch')->latest('id')->paginate(15),
            'assignments' => EmployeeShift::with('employee', 'shift')->latest('id')->take(20)->get(),
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function create()
    {
        return view('shifts.create', ['branches' => Branch::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Shift::create($this->validated($request));

        return redirect()->route('shifts.index')->with('success', 'Shift dibuat.');
    }

    public function edit(Shift $shift)
    {
        return view('shifts.edit', ['shift' => $shift, 'branches' => Branch::orderBy('name')->get()]);
    }

    public function update(Request $request, Shift $shift)
    {
        $shift->update($this->validated($request, $shift));

        return redirect()->route('shifts.index')->with('success', 'Shift diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return back()->with('success', 'Shift dihapus.');
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'effective_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
        ]);

        EmployeeShift::create($data);

        return back()->with('success', 'Shift ditugaskan ke karyawan.');
    }

    public function unassign(EmployeeShift $employee_shift)
    {
        $employee_shift->delete();

        return back()->with('success', 'Penugasan shift dihapus.');
    }

    protected function validated(Request $request, ?Shift $shift = null): array
    {
        return $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'unique:shifts,code'.($shift ? ','.$shift->id : '')],
            'name' => ['required', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
