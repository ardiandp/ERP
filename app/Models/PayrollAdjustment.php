<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    protected $fillable = ['payroll_id', 'type', 'description', 'amount', 'reason', 'created_by', 'approved_by', 'approved_at'];

    protected $casts = ['approved_at' => 'datetime'];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
