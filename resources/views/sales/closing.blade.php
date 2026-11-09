@extends('layouts.app')
@section('title', 'Tutup Kasir Harian - Dian Mustika ERP')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="mdi mdi-cash-register text-primary me-1"></i> Rekapitulasi &amp; Tutup Kasir Harian</h4>
        <small class="text-muted">Penyelarasan Penerimaan Kas Fisik, Rekapitulasi Metode Bayar, &amp; Transaksi Shift</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.closing.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-printer me-1"></i> Cetak Berita Acara Tutup Kasir
        </a>
        <a href="{{ route('sales.create') }}" class="btn btn-success btn-sm">
            <i class="mdi mdi-point-of-sale me-1"></i> Buka Layar POS
        </a>
    </div>
</div>

{{-- FILTER FORM --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sales.closing') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Tanggal Transaksi:</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cabang:</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Kasir Bertugas:</label>
                <select name="cashier_id" class="form-select form-select-sm">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiers as $c)
                    <option value="{{ $c->id }}" @selected($cashierId == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="mdi mdi-filter me-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- METRIC SUMMARY CARDS --}}
<div class="row g-2 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Total Penjualan</span>
                <span class="badge bg-primary rounded-circle p-2"><i class="mdi mdi-chart-line text-white"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</h4>
            <small class="text-muted">{{ $totalTransactions }} Transaksi Selesai</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Uang Tunai (Cash)</span>
                <span class="badge bg-success rounded-circle p-2"><i class="mdi mdi-cash text-white"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-success">Rp {{ number_format((float) $totalCash, 0, ',', '.') }}</h4>
            <small class="text-muted">Fisik kas laci kasir</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Non-Tunai / Bank</span>
                <span class="badge bg-info rounded-circle p-2"><i class="mdi mdi-bank-transfer text-white"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-info">Rp {{ number_format((float) $totalNonCash, 0, ',', '.') }}</h4>
            <small class="text-muted">BCA, QRIS, &amp; Kartu</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border-0 shadow-sm p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Total Item Terjual</span>
                <span class="badge bg-secondary rounded-circle p-2"><i class="mdi mdi-package-variant text-white"></i></span>
            </div>
            <h4 class="fw-bold mb-0 text-secondary">{{ (float) $totalItems }} Qty</h4>
            <small class="text-muted">Produk &bull; Treatment &bull; Paket</small>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- RINCIAN METODE PEMBAYARAN --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Rincian per Metode Bayar</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Metode Pembayaran</th>
                            <th class="text-end">Total Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentsByMethod as $methodName => $amount)
                        <tr>
                            <td>
                                <strong>{{ $methodName }}</strong>
                            </td>
                            <td class="text-end fw-bold">
                                Rp {{ number_format((float) $amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-3 text-muted">Belum ada pembayaran pada tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th class="fw-bold">Total Rekap:</th>
                            <th class="text-end fw-bold text-success">
                                Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- DAFTAR TRANSAKSI HARI INI --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Daftar Transaksi Kasir</h5>
                <span class="badge bg-primary">{{ $transactions->count() }} Invoice</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="closing-transactions-table">
                        <thead class="bg-light">
                            <tr>
                                <th>No. Invoice</th>
                                <th>Pelanggan</th>
                                <th>Kasir</th>
                                <th>Metode</th>
                                <th class="text-end">Total (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                            <tr>
                                <td>
                                    <strong>{{ $t->invoice_number }}</strong>
                                    <small class="text-muted d-block">{{ $t->created_at?->format('H:i') }}</small>
                                </td>
                                <td>{{ $t->customer?->name ?? 'Pelanggan Umum' }}</td>
                                <td>{{ $t->cashier?->name ?? 'Kasir' }}</td>
                                <td>
                                    @foreach($t->payments as $p)
                                    <span class="badge bg-light text-dark border">{{ $p->paymentMethod?->name ?? '-' }}</span>
                                    @endforeach
                                </td>
                                <td class="text-end fw-bold">
                                    Rp {{ number_format((float) $t->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('sales.show', $t) }}" class="btn btn-xs btn-outline-primary" title="Lihat Detail">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.print', $t) }}" target="_blank" class="btn btn-xs btn-outline-info" title="Cetak Struk">
                                        <i class="mdi mdi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi selesai pada tanggal yang dipilih.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

