<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\OvertimeRecord;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportService
{
    public function types(): array
    {
        return [
            'sales_daily' => 'Penjualan Harian',
            'sales_by_branch' => 'Penjualan per Cabang',
            'sales_by_cashier' => 'Penjualan per Kasir',
            'payment_method' => 'Penjualan per Metode Bayar',
            'product_sales' => 'Penjualan Produk',
            'service_sales' => 'Penjualan Jasa',
            'inventory_stock' => 'Stok Saat Ini',
            'low_stock' => 'Stok Menipis',
            'finance_cashflow' => 'Arus Kas',
            'expense_by_category' => 'Pengeluaran per Kategori',
            'bills_outstanding' => 'Tagihan Belum Lunas',
            'employee_count' => 'Jumlah Karyawan',
            'attendance_summary' => 'Rekap Absensi',
            'overtime_summary' => 'Rekap Lembur',
            'payroll_summary' => 'Rekap Payroll',
            'commission_summary' => 'Rekap Komisi',
            'top_customers' => 'Customer Teratas',
            'new_customers' => 'Customer Baru',
        ];
    }

    public function build(string $type, array $filters): array
    {
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();
        $branchId = $filters['branch_id'] ?? null;

        return match ($type) {
            'sales_daily' => $this->salesDaily($from, $to, $branchId),
            'sales_by_branch' => $this->salesByBranch($from, $to),
            'sales_by_cashier' => $this->salesByCashier($from, $to, $branchId),
            'payment_method' => $this->paymentMethod($from, $to),
            'product_sales' => $this->itemSales('PRODUCT', $from, $to, $branchId),
            'service_sales' => $this->itemSales('SERVICE', $from, $to, $branchId),
            'inventory_stock' => $this->inventoryStock($branchId, false),
            'low_stock' => $this->inventoryStock($branchId, true),
            'finance_cashflow' => $this->cashflow($from, $to, $branchId),
            'expense_by_category' => $this->expenseByCategory($from, $to, $branchId),
            'bills_outstanding' => $this->billsOutstanding($branchId),
            'employee_count' => $this->employeeCount($branchId),
            'attendance_summary' => $this->attendanceSummary($from, $to, $branchId),
            'overtime_summary' => $this->overtimeSummary($from, $to),
            'payroll_summary' => $this->payrollSummary(),
            'commission_summary' => $this->commissionSummary(),
            'top_customers' => $this->topCustomers($from, $to),
            'new_customers' => $this->newCustomers($from, $to),
            default => throw new InvalidArgumentException('Jenis laporan tidak dikenal.'),
        };
    }

    protected function salesDaily(string $from, string $to, ?int $branchId): array
    {
        $rows = SalesTransaction::where('sales_transactions.status', 'COMPLETED')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->selectRaw('DATE(transaction_date) as d, COUNT(*) as c, SUM(grand_total) as t')
            ->groupBy('d')->orderBy('d')->get()
            ->map(fn ($r) => [$r->d, (int) $r->c, (float) $r->t])->all();

        return $this->out('Penjualan Harian', ['Tanggal', 'Transaksi', 'Total'], $rows);
    }

    protected function salesByBranch(string $from, string $to): array
    {
        $rows = SalesTransaction::where('sales_transactions.status', 'COMPLETED')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->join('branches', 'branches.id', '=', 'sales_transactions.branch_id')
            ->selectRaw('branches.name as n, COUNT(*) as c, SUM(grand_total) as t')
            ->groupBy('branches.name')->orderByDesc('t')->get()
            ->map(fn ($r) => [$r->n, (int) $r->c, (float) $r->t])->all();

        return $this->out('Penjualan per Cabang', ['Cabang', 'Transaksi', 'Total'], $rows);
    }

    protected function salesByCashier(string $from, string $to, ?int $branchId): array
    {
        $rows = SalesTransaction::where('sales_transactions.status', 'COMPLETED')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->leftJoin('users', 'users.id', '=', 'sales_transactions.cashier_id')
            ->selectRaw('COALESCE(users.name, ?) as n, COUNT(*) as c, SUM(grand_total) as t', ['-'])
            ->groupBy('users.name')->orderByDesc('t')->get()
            ->map(fn ($r) => [$r->n, (int) $r->c, (float) $r->t])->all();

        return $this->out('Penjualan per Kasir', ['Kasir', 'Transaksi', 'Total'], $rows);
    }

    protected function paymentMethod(string $from, string $to): array
    {
        $rows = Payment::where('transaction_type', 'SALE')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->selectRaw('payment_methods.name as n, COUNT(*) as c, SUM(payments.amount) as t')
            ->groupBy('payment_methods.name')->orderByDesc('t')->get()
            ->map(fn ($r) => [$r->n, (int) $r->c, (float) $r->t])->all();

        return $this->out('Penjualan per Metode Bayar', ['Metode', 'Transaksi', 'Total'], $rows);
    }

    protected function itemSales(string $itemType, string $from, string $to, ?int $branchId): array
    {
        $rows = SalesTransactionItem::where('item_type', $itemType)
            ->join('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_items.sales_transaction_id')
            ->where('sales_transactions.status', 'COMPLETED')
            ->when($branchId, fn ($q) => $q->where('sales_transactions.branch_id', $branchId))
            ->whereDate('sales_transactions.transaction_date', '>=', $from)
            ->whereDate('sales_transactions.transaction_date', '<=', $to)
            ->selectRaw('sales_transaction_items.item_name as n, SUM(sales_transaction_items.quantity) as q, SUM(sales_transaction_items.subtotal) as t')
            ->groupBy('sales_transaction_items.item_name')->orderByDesc('t')->get()
            ->map(fn ($r) => [$r->n, (float) $r->q, (float) $r->t])->all();

        $title = $itemType === 'PRODUCT' ? 'Penjualan Produk' : 'Penjualan Jasa';

        return $this->out($title, ['Item', 'Qty', 'Total'], $rows);
    }

    protected function inventoryStock(?int $branchId, bool $lowOnly): array
    {
        $query = InventoryStock::join('products', 'products.id', '=', 'inventory_stocks.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'inventory_stocks.warehouse_id')
            ->when($branchId, fn ($q) => $q->where('warehouses.branch_id', $branchId))
            ->when($lowOnly, fn ($q) => $q->whereColumn('inventory_stocks.quantity', '<=', 'products.minimum_stock'))
            ->selectRaw('products.name as p, warehouses.name as w, inventory_stocks.quantity as q, products.purchase_price as c')
            ->orderBy('p');

        if ($lowOnly) {
            $rows = $query->get()->map(fn ($r) => [$r->p, $r->w, (float) $r->q, (float) $r->c]);
            $columns = ['Produk', 'Gudang', 'Qty', 'Harga Pokok'];
            $title = 'Stok Menipis';
        } else {
            $rows = $query->get()->map(fn ($r) => [$r->p, $r->w, (float) $r->q, (float) $r->c, (float) $r->q * (float) $r->c]);
            $columns = ['Produk', 'Gudang', 'Qty', 'Harga Pokok', 'Nilai'];
            $title = 'Stok Saat Ini';
        }

        return $this->out($title, $columns, $rows->all());
    }

    protected function cashflow(string $from, string $to, ?int $branchId): array
    {
        $in = Payment::where('transaction_type', 'SALE')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->selectRaw('DATE(payment_date) as d, SUM(amount) as t')
            ->groupBy('d')->pluck('t', 'd');

        $out = Expense::whereIn('status', ['APPROVED', 'PAID'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->selectRaw('DATE(expense_date) as d, SUM(amount) as t')
            ->groupBy('d')->pluck('t', 'd');

        $dates = $in->keys()->merge($out->keys())->unique()->sort()->values();

        $rows = $dates->map(function ($date) use ($in, $out) {
            $income = (float) ($in[$date] ?? 0);
            $expense = (float) ($out[$date] ?? 0);

            return [$date, $income, $expense, $income - $expense];
        })->all();

        return $this->out('Arus Kas', ['Tanggal', 'Masuk', 'Keluar', 'Net'], $rows);
    }

    protected function expenseByCategory(string $from, string $to, ?int $branchId): array
    {
        $rows = Expense::whereIn('expenses.status', ['APPROVED', 'PAID'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->selectRaw('expense_categories.name as n, SUM(expenses.amount) as t')
            ->groupBy('expense_categories.name')->orderByDesc('t')->get()
            ->map(fn ($r) => [$r->n, (float) $r->t])->all();

        return $this->out('Pengeluaran per Kategori', ['Kategori', 'Jumlah'], $rows);
    }

    protected function billsOutstanding(?int $branchId): array
    {
        $rows = Bill::where('status', 'UNPAID')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('due_date')->get()
            ->map(fn ($b) => [$b->bill_number, optional($b->due_date)->format('Y-m-d'), (float) $b->total_amount, $b->status])->all();

        return $this->out('Tagihan Belum Lunas', ['No Tagihan', 'Jatuh Tempo', 'Jumlah', 'Status'], $rows);
    }

    protected function employeeCount(?int $branchId): array
    {
        $rows = Employee::where('employees.status', 'ACTIVE')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('branches', 'branches.id', '=', 'employees.branch_id')
            ->selectRaw('branches.name as n, COUNT(*) as c')
            ->groupBy('branches.name')->orderBy('n')->get()
            ->map(fn ($r) => [$r->n, (int) $r->c])->all();

        return $this->out('Jumlah Karyawan', ['Cabang', 'Jumlah'], $rows);
    }

    protected function attendanceSummary(string $from, string $to, ?int $branchId): array
    {
        $rows = DB::table('attendances')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('attendance_date', '>=', $from)
            ->whereDate('attendance_date', '<=', $to)
            ->selectRaw('status as n, COUNT(*) as c')
            ->groupBy('status')->orderByDesc('c')->get()
            ->map(fn ($r) => [$r->n, (int) $r->c])->all();

        return $this->out('Rekap Absensi', ['Status', 'Jumlah'], $rows);
    }

    protected function overtimeSummary(string $from, string $to): array
    {
        $rows = OvertimeRecord::whereDate('overtime_date', '>=', $from)
            ->whereDate('overtime_date', '<=', $to)
            ->join('employees', 'employees.id', '=', 'overtime_records.employee_id')
            ->selectRaw('employees.full_name as n, SUM(overtime_records.duration_minutes) as m, COUNT(*) as c')
            ->groupBy('employees.full_name')->orderByDesc('m')->get()
            ->map(fn ($r) => [$r->n, (int) $r->m, (int) $r->c])->all();

        return $this->out('Rekap Lembur', ['Karyawan', 'Total Menit', 'Jumlah Hari'], $rows);
    }

    protected function payrollSummary(): array
    {
        $rows = Payroll::join('payroll_periods', 'payroll_periods.id', '=', 'payrolls.payroll_period_id')
            ->selectRaw('payroll_periods.name as n, COUNT(*) as c, SUM(payrolls.net_salary) as t')
            ->groupBy('payroll_periods.name')->orderBy('n')->get()
            ->map(fn ($r) => [$r->n, (int) $r->c, (float) $r->t])->all();

        return $this->out('Rekap Payroll', ['Periode', 'Karyawan', 'Total Net'], $rows);
    }

    protected function commissionSummary(): array
    {
        $rows = EmployeeCommission::join('employees', 'employees.id', '=', 'employee_commissions.employee_id')
            ->selectRaw('employees.full_name as n, SUM(employee_commissions.commission_amount) as t, COUNT(*) as c')
            ->groupBy('employees.full_name')->orderByDesc('t')->get()
            ->map(fn ($r) => [$r->n, (float) $r->t, (int) $r->c])->all();

        return $this->out('Rekap Komisi', ['Karyawan', 'Total Komisi', 'Jumlah'], $rows);
    }

    protected function topCustomers(string $from, string $to): array
    {
        $rows = SalesTransaction::where('sales_transactions.status', 'COMPLETED')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->whereNotNull('customer_id')
            ->join('customers', 'customers.id', '=', 'sales_transactions.customer_id')
            ->selectRaw('customers.name as n, COUNT(*) as c, SUM(grand_total) as t')
            ->groupBy('customers.name')->orderByDesc('t')->limit(20)->get()
            ->map(fn ($r) => [$r->n, (int) $r->c, (float) $r->t])->all();

        return $this->out('Customer Teratas', ['Customer', 'Transaksi', 'Total'], $rows);
    }

    protected function newCustomers(string $from, string $to): array
    {
        $rows = Customer::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->orderBy('d')->get()
            ->map(fn ($r) => [$r->d, (int) $r->c])->all();

        return $this->out('Customer Baru', ['Tanggal', 'Jumlah'], $rows);
    }

    protected function out(string $title, array $columns, array $rows): array
    {
        return ['title' => $title, 'columns' => $columns, 'rows' => $rows];
    }
}
