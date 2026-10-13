<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = ['payroll_id', 'salary_component_id', 'component_name', 'type', 'amount', 'source_type', 'source_id', 'notes', 'sort_order'];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
