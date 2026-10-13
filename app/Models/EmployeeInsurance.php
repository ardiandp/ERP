<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeInsurance extends Model
{
    protected $fillable = ['employee_id', 'insurance_type', 'provider', 'membership_number', 'insurance_class', 'start_date', 'end_date', 'employee_contribution', 'company_contribution', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
