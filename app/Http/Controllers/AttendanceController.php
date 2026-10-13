<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendance) {}

    public function index(Request $request)
    {
        $attendances = Attendance::with('employee', 'branch')
            ->when($request->filled('date'), fn ($query) => $query->whereDate('attendance_date', $request->input('date')))
            ->when($request->filled('employee_id'), fn ($query) => $query->where('employee_id', $request->input('employee_id')))
            ->latest('attendance_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('attendances.index', [
            'attendances' => $attendances,
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function create()
    {
        return view('attendances.create', [
            'employees' => Employee::orderBy('full_name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:PRESENT,ABSENT,LATE,LEAVE,SICK,HOLIDAY'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->attendance->record($data);

        return redirect()->route('attendances.index')->with('success', 'Absensi dicatat.');
    }
}
