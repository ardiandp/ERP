<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita Acara Tutup Kasir - {{ $date }} - Dian Mustika ERP</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-size: 13px; color: #212529; }
        .closing-container { max-width: 720px; margin: 20px auto; background: #fff; padding: 36px 40px; box-shadow: 0 0 10px rgba(0,0,0,0.08); border-radius: 4px; }
        .company-header h3 { margin: 0; font-weight: 700; color: #1f262d; }
        .table-closing th { background-color: #f1f3f5 !important; font-weight: 600; font-size: 12px; }
        .table-closing td { vertical-align: middle; }
        .sign-box { border-top: 1px dashed #6c757d; margin-top: 55px; padding-top: 6px; font-weight: 600; font-size: 12px; }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .closing-container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 15px 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print py-2 bg-dark text-white mb-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 720px;">
        <span class="small"><i class="mdi mdi-printer"></i> Pratinjau Berita Acara Tutup Kasir: <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</strong></span>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-primary me-2"><i class="mdi mdi-printer"></i> Cetak Dokumen</button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">Tutup</button>
        </div>
    </div>
</div>

<div class="closing-container">
    {{-- KOP SURAT --}}
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div class="company-header">
            <h3>DIAN MUSTIKA</h3>
            <p class="mb-0 text-muted small fw-bold">SPA &bull; WELLNESS &bull; BEAUTY TREATMENT</p>
            <small class="text-muted d-block">{{ $branch ? $branch->name : 'Semua Cabang Operasional' }}</small>
        </div>
        <div class="text-end">
            <h5 class="text-uppercase fw-bold text-dark mb-0">BERITA ACARA TUTUP KASIR</h5>
            <small class="text-muted d-block">DAILY CASHIER CLOSING REPORT</small>
            <small class="text-muted d-block mt-1">Tanggal Cetak: {{ now()->translatedFormat('d/m/Y H:i') }}</small>
        </div>
    </div>

    {{-- INFORMASI SHIFT --}}
    <table class="table table-sm table-borderless mb-3">
        <tr>
            <th class="text-muted small" style="width: 25%;">Tanggal Penjualan:</th>
            <td class="fw-bold" style="width: 35%;">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</td>
            <th class="text-muted small" style="width: 20%;">Kasir Bertugas:</th>
            <td class="fw-bold">{{ $cashier ? $cashier->name : (auth()->user()?->name ?? 'Kasir') }}</td>
        </tr>
        <tr>
            <th class="text-muted small">Cabang:</th>
            <td>{{ $branch ? $branch->name : 'Semua Cabang' }}</td>
            <th class="text-muted small">Total Transaksi:</th>
            <td class="fw-bold text-primary">{{ $totalTransactions }} Invoice Selesai</td>
        </tr>
    </table>

    {{-- REKAPITULASI METODE PEMBAYARAN --}}
    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">1. Rekapitulasi Metode Penerimaan</h6>
    <table class="table table-bordered table-sm table-closing mb-3">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 55%;">Metode Pembayaran</th>
                <th style="width: 40%;" class="text-end">Jumlah Diterima (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php($no = 1)
            @forelse($paymentsByMethod as $methodName => $amount)
            <tr>
                <td class="text-center text-muted">{{ $no++ }}</td>
                <td><strong>{{ $methodName }}</strong></td>
                <td class="text-end fw-bold">Rp {{ number_format((float) $amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center py-2 text-muted">Belum ada transaksi pembayaran.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="table-primary">
                <th colspan="2" class="text-end fw-bold fs-6">TOTAL PENJUALAN KASIR:</th>
                <td class="text-end fw-bold fs-6">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- REKONSILIASI KAS FISIK LACI --}}
    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">2. Rekonsiliasi Kas Fisik Laci (Cash Drawer)</h6>
    <table class="table table-bordered table-sm mb-4">
        <tr>
            <th class="bg-light text-muted" style="width: 60%;">Penerimaan Tunai Sistem (Cash):</th>
            <td class="text-end fw-bold text-success fs-6">Rp {{ number_format((float) $totalCash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th class="bg-light text-muted">Penghitungan Uang Fisik di Laci:</th>
            <td class="text-end text-muted">( ........................................ )</td>
        </tr>
        <tr>
            <th class="bg-light text-muted">Selisih Kas Fisik vs Sistem (+ / -):</th>
            <td class="text-end text-muted">( ........................................ )</td>
        </tr>
    </table>

    {{-- TANDA TANGAN SERAH TERIMA --}}
    <div class="row text-center mt-5">
        <div class="col-6">
            <div class="text-muted small">Dibuat &amp; Dihitung Oleh:</div>
            <div class="sign-box">{{ $cashier ? $cashier->name : auth()->user()?->name }}</div>
            <small class="text-muted">Kasir Bertugas</small>
        </div>
        <div class="col-6">
            <div class="text-muted small">Diverifikasi &amp; Diterima Oleh:</div>
            <div class="sign-box">( ........................................ )</div>
            <small class="text-muted">Supervisor / Branch Manager</small>
        </div>
    </div>
</div>

</body>
</html>

