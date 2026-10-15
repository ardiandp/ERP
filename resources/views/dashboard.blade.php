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

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Tren Penjualan (14 Hari Terakhir)</div>
            <div class="card-body"><div id="chart-sales-trend" style="height:280px;"></div></div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header">Penjualan per Cabang (Bulan Ini)</div>
            <div class="card-body"><div id="chart-branch-pie" style="height:280px;"></div></div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header">Metode Pembayaran (Bulan Ini)</div>
            <div class="card-body"><div id="chart-payment-pie" style="height:280px;"></div></div>
        </div>
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
                    <td>{{ $row['branch'] }}</td>
                    <td class="text-end">{{ number_format($row['total_sales'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ $row['total_transactions'] }}</td>
                </tr>
                @empty
                <tr><td colspan="3">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('theme/matrix-admin/assets/libs/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('theme/matrix-admin/assets/libs/flot/jquery.flot.pie.js') }}"></script>
<script src="{{ asset('theme/matrix-admin/assets/libs/flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('theme/matrix-admin/assets/libs/jquery.flot.tooltip/js/jquery.flot.tooltip.min.js') }}"></script>
<script>
$(function () {
    var trend = @json($metrics['sales_trend']);
    var branches = @json($metrics['branch_performance']);
    var payments = @json($metrics['payment_mix']);

    var fmt = function (v) { return Number(v).toLocaleString('id-ID'); };

    // Bar chart: sales trend
    var ticks = trend.map(function (p, i) { return [i, p.label]; });
    var bars = trend.map(function (p, i) { return [i, p.value]; });
    if (bars.length) {
        $.plot('#chart-sales-trend', [{ data: bars, color: '#2962ff', bars: { show: true, barWidth: 0.6, align: 'center', fillColor: '#2962ff' } }], {
            xaxis: { ticks: ticks, tickDecimals: 0 },
            yaxis: { min: 0, tickFormatter: fmt },
            grid: { borderWidth: 0, hoverable: true, margin: 8 },
            tooltip: true,
            tooltipOpts: { content: '%x : Rp %y', defaultTheme: false }
        });
    }

    var pieOpts = {
        series: { pie: { show: true, radius: 0.9, label: { show: false } } },
        legend: { show: true, position: 'ne', backgroundOpacity: 0.85 },
        grid: { hoverable: true, borderWidth: 0 },
        tooltip: true,
        tooltipOpts: { content: '%s: Rp %y (%p.1%)', defaultTheme: false }
    };

    var branchData = branches.map(function (b) { return { label: b.branch, data: b.total_sales }; });
    if (branchData.length) {
        $.plot('#chart-branch-pie', branchData, pieOpts);
    } else {
        $('#chart-branch-pie').html('<p class="text-muted text-center m-t-40">Belum ada data.</p>');
    }

    var payData = payments.map(function (p) { return { label: p.method, data: p.total }; });
    if (payData.length) {
        $.plot('#chart-payment-pie', payData, pieOpts);
    } else {
        $('#chart-payment-pie').html('<p class="text-muted text-center m-t-40">Belum ada data.</p>');
    }
});
</script>
@endpush
@endsection
