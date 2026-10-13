<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryComponent extends Model
{
    protected $fillable = ['employee_id', 'salary_component_id', 'amount', 'calculation_type', 'effective_date', 'end_date', 'status'];

    protected $casts = ['effective_date' => 'date', 'end_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
