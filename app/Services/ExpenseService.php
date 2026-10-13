<?php

namespace App\Services;

use App\Models\Expense;
use App\Support\DocumentNumber;
use InvalidArgumentException;

class ExpenseService
{
    public function create(array $data): Expense
    {
        $expense = Expense::create([
            'expense_number' => DocumentNumber::generate('EXP', Expense::class, 'expense_number', $data['expense_date'] ?? null),
            'branch_id' => $data['branch_id'] ?? null,
            'expense_category_id' => $data['expense_category_id'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'] ?? now()->toDateString(),
            'financial_account_id' => $data['financial_account_id'] ?? null,
            'bill_id' => $data['bill_id'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'DRAFT',
            'created_by' => auth()->id(),
        ]);

        return $expense;
    }

    public function approve(Expense $expense): Expense
    {
        if ($expense->status !== 'DRAFT') {
            throw new InvalidArgumentException('Hanya expense berstatus DRAFT yang dapat disetujui.');
        }

        $expense->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $expense;
    }
}
