<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRecord extends Model
{
    protected $fillable = ['employee_id', 'attendance_id', 'overtime_date', 'start_time', 'end_time', 'duration_minutes', 'approved_by', 'status'];

    protected $casts = ['overtime_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
