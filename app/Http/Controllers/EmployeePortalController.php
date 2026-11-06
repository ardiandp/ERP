<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\OvertimeRecord;
use App\Models\Payslip;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService) {}

    public function profile()
    {
        $user = auth()->user()->load([
            'employee.branch',
            'employee.department',
            'employee.position',
            'employee.familyMembers',
            'employee.insurances',
        ]);

        $employee = $user->employee;

        return view('portal.profile', compact('user', 'employee'));
    }

    public function attendance(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('portal.profile')->with('error', 'Akun pengguna ini belum ditautkan dengan data karyawan.');
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->latest('attendance_date')
            ->get();

        $overtimes = OvertimeRecord::where('employee_id', $employee->id)
            ->whereBetween('overtime_date', [$monthStart, $monthEnd])
            ->latest('overtime_date')
            ->get();

        $stats = [
            'present' => $attendances->where('status', 'PRESENT')->count(),
            'late' => $attendances->where('status', 'LATE')->count(),
            'leave' => $attendances->whereIn('status', ['LEAVE', 'SICK', 'PERMISSION'])->count(),
            'overtime_hours' => $overtimes->where('status', 'APPROVED')->sum('hours'),
        ];

        return view('portal.attendance', compact('employee', 'attendances', 'overtimes', 'stats'));
    }

    public function submitOvertime(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return back()->with('error', 'Akun tidak ditautkan dengan data karyawan.');
        }

        $data = $request->validate([
            'overtime_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        $data['employee_id'] = $employee->id;

        $record = $this->attendanceService->recordOvertime($data);

        AuditLog::record('create', $record, [], [
            'employee_id' => $employee->id,
            'overtime_date' => $data['overtime_date'],
            'status' => 'PENDING',
        ]);

        return back()->with('success', 'Pengajuan lembur berhasil diajukan dan sedang menunggu persetujuan atasan.');
    }

    public function payslips()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('portal.profile')->with('error', 'Akun pengguna ini belum ditautkan dengan data karyawan.');
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->where('status', 'PUBLISHED')
            ->with(['payroll.period'])
            ->latest('issue_date')
            ->get();

        return view('portal.payslips', compact('employee', 'payslips'));
    }
}
