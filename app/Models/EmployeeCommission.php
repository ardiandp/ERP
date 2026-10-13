<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCommission extends Model
{
    protected $fillable = ['employee_id', 'sales_transaction_id', 'sales_transaction_item_id', 'commission_amount', 'status', 'payroll_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }
}
