<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = ['employee_id', 'service_id', 'commission_type', 'commission_value', 'effective_date', 'end_date', 'status'];

    protected $casts = ['effective_date' => 'date', 'end_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
