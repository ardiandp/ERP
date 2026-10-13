<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $fillable = ['code', 'name', 'period_start', 'period_end', 'payment_date', 'status'];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'payment_date' => 'date'];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
