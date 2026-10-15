@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Dashboard</h4>
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2">
        <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Cabang</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected($selectedBranch === $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card border-primary"><div class="card-body">
            <div class="text-muted small">Omzet Hari Ini</div>
            <div class="fs-5 fw-bold">{{ number_format($metrics['sales_today'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary"><div class="card-body">
            <div class="text-muted small">Omzet Bulan Ini</div>
            <div class="fs-5 fw-bold">{{ number_format($metrics['sales_month'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Transaksi Hari Ini</div>
            <div class="fs-5 fw-bold">{{ $metrics['transactions_today'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning"><div class="card-body">
            <div class="text-muted small">Outstanding Bills</div>
            <div class="fs-5 fw-bold">{{ number_format($metrics['outstanding_bills'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Cash Balance</div>
            <div class="fs-5 fw-bold">{{ number_format($metrics['cash_balance'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Bank Balance</div>
            <div class="fs-5 fw-bold">{{ number_format($metrics['bank_balance'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="card border-danger"><div class="card-body">
            <div class="text-muted small">Low Stock</div>
            <div class="fs-5 fw-bold">{{ $metrics['low_stock_count'] }}</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Payroll Pending</div>
            <div class="fs-5 fw-bold">{{ $metrics['payroll_pending'] }}</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Approval Pending</div>
            <div class="fs-5 fw-bold">{{ $metrics['approval_pending'] }}</div>
        </div></div>
    </div>
</div>

@if($branchMetrics)
<h5 class="mt-4">Branch Dashboard</h5>
<div class="row g-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Branch Sales (Bulan)</div><div class="fs-6 fw-bold">{{ number_format($branchMetrics['sales_month'], 0, ',', '.') }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Transaksi (Bulan)</div><div class="fs-6 fw-bold">{{ $branchMetrics['transactions_month'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Booking Hari Ini</div><div class="fs-6 fw-bold">{{ $branchMetrics['bookings_today'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Absensi Hari Ini</div><div class="fs-6 fw-bold">{{ $branchMetrics['attendance_today'] }}</div></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><div class="text-muted small">Low Stock</div><div class="fs-6 fw-bold">{{ $branchMetrics['low_stock_count'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Expense (Bulan)</div><div class="fs-6 fw-bold">{{ number_format($branchMetrics['expenses_month'], 0, ',', '.') }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Karyawan Aktif</div><div class="fs-6 fw-bold">{{ $branchMetrics['employee_count'] }}</div></div></div></div>
</div>
@endif

<div class="card mt-4">
    <div class="card-header">Branch Performance (Bulan Ini)</div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Cabang</th><th class="text-end">Omzet</th><th class="text-end">Transaksi</th></tr></thead>
            <tbody>
                @forelse($metrics['branch_performance'] as $row)
                <tr>
                    <td>{{ $row->branch?->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format((float) $row->total_sales, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $row->total_transactions }}</td>
                </tr>
                @empty
                <tr><td colspan="3">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
