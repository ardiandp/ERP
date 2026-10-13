<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['branch_id', 'code', 'name', 'start_time', 'end_time', 'break_minutes', 'status'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }
}
