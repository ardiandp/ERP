<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OvertimeController extends Controller
{
    public function __construct(protected AttendanceService $attendance) {}

    public function index()
    {
        return view('overtimes.index', [
            'overtimes' => OvertimeRecord::with('employee')->latest('overtime_date')->latest('id')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('overtimes.create', [
            'employees' => Employee::orderBy('full_name')->get(),
            'attendances' => Attendance::with('employee')->latest('attendance_date')->take(100)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_id' => ['nullable', 'exists:attendances,id'],
            'overtime_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        $this->attendance->recordOvertime($data);

        return redirect()->route('overtimes.index')->with('success', 'Lembur dicatat.');
    }

    public function approve(OvertimeRecord $overtime)
    {
        try {
            $this->attendance->approveOvertime($overtime);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('success', 'Lembur disetujui.');
    }
}
