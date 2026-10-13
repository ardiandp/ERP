<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['expense_number', 'branch_id', 'expense_category_id', 'supplier_id', 'amount', 'expense_date', 'financial_account_id', 'bill_id', 'description', 'status', 'created_by', 'approved_by', 'approved_at'];

    protected $casts = ['expense_date' => 'date', 'approved_at' => 'datetime'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function financialAccount()
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
