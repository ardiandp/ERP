<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Bill;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\SalesTransaction;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(protected FinanceService $finance) {}

    public function owner(?int $branchId = null): array
    {
        return Cache::remember('dashboard.owner.'.($branchId ?? 'all'), now()->addMinute(), fn () => $this->buildOwner($branchId));
    }

    protected function buildOwner(?int $branchId = null): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $balances = $this->finance->accountsWithBalances();

        return [
            'sales_today' => (float) SalesTransaction::where('status', 'COMPLETED')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereDate('transaction_date', $today)
                ->sum('grand_total'),
            'sales_month' => (float) SalesTransaction::where('status', 'COMPLETED')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('grand_total'),
            'transactions_today' => SalesTransaction::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereDate('transaction_date', $today)
                ->count(),
            'outstanding_bills' => (float) Bill::where('status', 'UNPAID')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount'),
            'cash_balance' => (float) $balances->where('account_type', 'CASH')->sum('computed_balance'),
            'bank_balance' => (float) $balances->where('account_type', 'BANK')->sum('computed_balance'),
            'low_stock_count' => $this->lowStockQuery($branchId)->count(),
            'payroll_pending' => Payroll::whereIn('status', ['DRAFT', 'APPROVED'])
                ->when($branchId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('branch_id', $branchId)))
                ->count(),
            'approval_pending' => Expense::where('status', 'DRAFT')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->count()
                + PurchaseOrder::where('status', 'DRAFT')
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->count(),
            'branch_performance' => SalesTransaction::where('status', 'COMPLETED')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->selectRaw('branch_id, SUM(grand_total) as total_sales, COUNT(*) as total_transactions')
                ->groupBy('branch_id')
                ->with('branch')
                ->orderByDesc('total_sales')
                ->get()
                ->map(fn ($row) => [
                    'branch' => $row->branch?->name ?? '-',
                    'total_sales' => (float) $row->total_sales,
                    'total_transactions' => (int) $row->total_transactions,
                ])
                ->all(),
            'sales_trend' => $this->salesTrend($branchId, 14),
            'payment_mix' => $this->paymentMix($branchId),
        ];
    }

    protected function salesTrend(?int $branchId, int $days): array
    {
        $start = now()->subDays($days - 1)->toDateString();
        $end = now()->toDateString();

        $rows = SalesTransaction::where('status', 'COMPLETED')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('transaction_date', '>=', $start)
            ->whereDate('transaction_date', '<=', $end)
            ->selectRaw('DATE(transaction_date) as d, SUM(grand_total) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $series[] = [
                'label' => $date->format('d M'),
                'value' => (float) ($rows[$date->toDateString()] ?? 0),
            ];
        }

        return $series;
    }

    protected function paymentMix(?int $branchId): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        return Payment::query()
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->where('payments.transaction_type', 'SALE')
            ->when($branchId, fn ($q) => $q->whereIn('payments.transaction_id', function ($sub) use ($branchId) {
                $sub->select('id')->from('sales_transactions')->where('branch_id', $branchId);
            }))
            ->whereDate('payments.payment_date', '>=', $monthStart)
            ->whereDate('payments.payment_date', '<=', $monthEnd)
            ->selectRaw('payment_methods.name as method, SUM(payments.amount) as total')
            ->groupBy('payment_methods.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'method' => $row->method,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    public function branch(int $branchId): array
    {
        return Cache::remember('dashboard.branch.'.$branchId, now()->addMinute(), fn () => $this->buildBranch($branchId));
    }

    protected function buildBranch(int $branchId): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        return [
            'sales_today' => (float) SalesTransaction::where('status', 'COMPLETED')
                ->where('branch_id', $branchId)
                ->whereDate('transaction_date', $today)
                ->sum('grand_total'),
            'sales_month' => (float) SalesTransaction::where('status', 'COMPLETED')
                ->where('branch_id', $branchId)
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('grand_total'),
            'transactions_month' => SalesTransaction::where('branch_id', $branchId)
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->count(),
            'bookings_today' => Booking::where('branch_id', $branchId)
                ->whereDate('booking_date', $today)
                ->count(),
            'attendance_today' => Attendance::where('branch_id', $branchId)
                ->whereDate('attendance_date', $today)
                ->count(),
            'expenses_month' => (float) Expense::where('branch_id', $branchId)
                ->whereIn('status', ['APPROVED', 'PAID'])
                ->whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount'),
            'low_stock_count' => $this->lowStockQuery($branchId)->count(),
            'employee_count' => Employee::where('branch_id', $branchId)->where('status', 'ACTIVE')->count(),
        ];
    }

    protected function lowStockQuery(?int $branchId)
    {
        return InventoryStock::query()
            ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
            ->when($branchId, fn ($q) => $q->whereHas('warehouse', fn ($w) => $w->where('branch_id', $branchId)))
            ->whereColumn('inventory_stocks.quantity', '<=', 'products.minimum_stock');
    }
}
