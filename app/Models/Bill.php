<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = ['bill_template_id', 'bill_number', 'branch_id', 'period_start', 'period_end', 'issue_date', 'due_date', 'amount', 'penalty_amount', 'total_amount', 'status', 'paid_at', 'notes'];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'issue_date' => 'date', 'due_date' => 'date', 'paid_at' => 'datetime'];

    public function template()
    {
        return $this->belongsTo(BillTemplate::class, 'bill_template_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
