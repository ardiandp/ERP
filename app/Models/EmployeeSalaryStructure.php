<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructure extends Model
{
    protected $fillable = ['employee_id', 'effective_date', 'basic_salary', 'notes', 'status'];

    protected $casts = ['effective_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
