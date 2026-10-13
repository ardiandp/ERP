<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = ['employee_code', 'nik', 'full_name', 'birth_place', 'birth_date', 'gender', 'phone', 'email', 'address', 'marital_status', 'employment_status', 'join_date', 'resign_date', 'branch_id', 'department_id', 'position_id', 'manager_id', 'status'];

    protected $casts = ['birth_date' => 'date', 'join_date' => 'date', 'resign_date' => 'date'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function familyMembers()
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    public function insurances()
    {
        return $this->hasMany(EmployeeInsurance::class);
    }

    public function commissions()
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function bookings()
    {
        return $this->hasMany(BookingItem::class);
    }
}
