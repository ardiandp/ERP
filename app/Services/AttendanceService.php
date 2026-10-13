<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\OvertimeRecord;
use Carbon\Carbon;
use InvalidArgumentException;

class AttendanceService
{
    public function record(array $data): Attendance
    {
        return Attendance::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'attendance_date' => $data['attendance_date'],
            ],
            [
                'branch_id' => $data['branch_id'],
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'status' => $data['status'] ?? 'PRESENT',
                'source' => $data['source'] ?? 'MANUAL',
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function recordOvertime(array $data): OvertimeRecord
    {
        $start = Carbon::parse($data['overtime_date'].' '.$data['start_time']);
        $end = Carbon::parse($data['overtime_date'].' '.$data['end_time']);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $duration = (int) $start->diffInMinutes($end);

        return OvertimeRecord::create([
            'employee_id' => $data['employee_id'],
            'attendance_id' => $data['attendance_id'] ?? null,
            'overtime_date' => $data['overtime_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration_minutes' => $duration,
            'status' => 'PENDING',
        ]);
    }

    public function approveOvertime(OvertimeRecord $record): OvertimeRecord
    {
        if ($record->status === 'APPROVED') {
            throw new InvalidArgumentException('Lembur sudah disetujui.');
        }

        $record->update(['status' => 'APPROVED', 'approved_by' => auth()->id()]);

        return $record;
    }
}
