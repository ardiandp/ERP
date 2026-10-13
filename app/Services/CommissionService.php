<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\EmployeeCommission;
use App\Models\SalesTransactionItem;

class CommissionService
{
    public function computeForItem(SalesTransactionItem $item): ?EmployeeCommission
    {
        if (! $item->employee_id || $item->item_type !== 'SERVICE' || ! $item->service_id) {
            return null;
        }

        $rule = $this->resolveRule((int) $item->employee_id, (int) $item->service_id);

        if (! $rule) {
            return null;
        }

        $base = (float) $item->subtotal;
        $amount = $rule->commission_type === 'PERCENTAGE'
            ? $base * ((float) $rule->commission_value / 100)
            : (float) $rule->commission_value;

        if ($amount <= 0) {
            return null;
        }

        return EmployeeCommission::create([
            'employee_id' => $item->employee_id,
            'sales_transaction_id' => $item->sales_transaction_id,
            'sales_transaction_item_id' => $item->id,
            'commission_amount' => round($amount, 2),
            'status' => 'PENDING',
        ]);
    }

    protected function resolveRule(int $employeeId, int $serviceId): ?CommissionRule
    {
        $today = now()->toDateString();

        $rules = CommissionRule::query()
            ->where('status', 'ACTIVE')
            ->where('effective_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->where(function ($query) use ($employeeId, $serviceId) {
                $query->where(function ($q) use ($employeeId, $serviceId) {
                    $q->where('employee_id', $employeeId)->where('service_id', $serviceId);
                })->orWhere(function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId)->whereNull('service_id');
                })->orWhere(function ($q) use ($serviceId) {
                    $q->whereNull('employee_id')->where('service_id', $serviceId);
                })->orWhere(function ($q) {
                    $q->whereNull('employee_id')->whereNull('service_id');
                });
            })
            ->get();

        return $rules
            ->sortByDesc(fn (CommissionRule $rule) => ($rule->employee_id ? 2 : 0) + ($rule->service_id ? 1 : 0))
            ->first();
    }
}
