<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = ['payroll_id', 'employee_id', 'template_id', 'payslip_number', 'issue_date', 'gross_salary', 'total_deduction', 'net_salary', 'status', 'pdf_path', 'approved_by', 'approved_at', 'verification_code'];

    protected $casts = ['issue_date' => 'date', 'approved_at' => 'datetime'];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(PayslipItem::class);
    }
}
