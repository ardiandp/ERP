<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = ['payroll_period_id', 'employee_id', 'basic_salary', 'total_earnings', 'total_deductions', 'net_salary', 'status', 'generated_at', 'approved_by', 'approved_at'];

    protected $casts = ['generated_at' => 'datetime', 'approved_at' => 'datetime'];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function adjustments()
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }
}
