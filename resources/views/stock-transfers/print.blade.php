<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan Transfer {{ $transfer->transfer_number }} - Dian Mustika ERP</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-size: 13px; color: #212529; }
        .do-container { max-width: 820px; margin: 20px auto; background: #fff; padding: 36px 40px; box-shadow: 0 0 10px rgba(0,0,0,0.08); border-radius: 4px; }
        .company-header h3 { margin: 0; font-weight: 700; color: #28b779; }
        .table-items th { background-color: #28b779 !important; color: #fff !important; font-weight: 600; text-align: center; font-size: 12px; }
        .table-items td { vertical-align: middle; }
        .sign-box { border-top: 1px dashed #6c757d; margin-top: 55px; padding-top: 6px; font-weight: 600; font-size: 12px; }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .do-container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 15px 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print py-2 bg-dark text-white mb-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 820px;">
        <span class="small"><i class="mdi mdi-printer"></i> Pratinjau Surat Jalan Transfer: <strong>{{ $transfer->transfer_number }}</strong></span>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-success me-2 text-white"><i class="mdi mdi-printer"></i> Cetak Surat Jalan</button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">Tutup</button>
        </div>
    </div>
</div>

<div class="do-container">
    {{-- KOP SURAT --}}
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div class="company-header">
            <h3>DIAN MUSTIKA</h3>
            <p class="mb-0 text-muted small fw-bold">SPA &bull; WELLNESS &bull; LOGISTICS &amp; SUPPLY</p>
            <small class="text-muted d-block">{{ $transfer->fromWarehouse?->branch?->company?->name ?? 'PT Dian Mustika Pratama' }}</small>
            <small class="text-muted d-block">{{ $transfer->fromWarehouse?->address ?? 'Pusat Distribusi Spa Dian Mustika' }}</small>
        </div>
        <div class="text-end">
            <h4 class="text-uppercase fw-bold text-dark mb-1">SURAT JALAN</h4>
            <div class="text-muted small">DELIVERY ORDER / STOCK TRANSFER</div>
            <div class="mt-2 text-success fw-bold fs-5">{{ $transfer->transfer_number }}</div>
            <span class="badge {{ $transfer->status === 'COMPLETED' ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ $transfer->status }}
            </span>
        </div>
    </div>

    {{-- INFORMASI GUDANG ASAL & TUJUAN --}}
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="p-2 border rounded bg-light">
                <strong class="d-block text-uppercase text-muted small mb-1">Gudang Pengirim (Asal):</strong>
                <div class="fw-bold fs-6">{{ $transfer->fromWarehouse?->name }}</div>
                <div class="small text-muted">Cabang: {{ $transfer->fromWarehouse?->branch?->name }}</div>
                <div class="small text-muted">{{ $transfer->fromWarehouse?->address ?? '-' }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-2 border rounded bg-light">
                <strong class="d-block text-uppercase text-muted small mb-1">Gudang Penerima (Tujuan):</strong>
                <div class="fw-bold fs-6">{{ $transfer->toWarehouse?->name }}</div>
                <div class="small text-muted">Cabang: {{ $transfer->toWarehouse?->branch?->name }}</div>
                <div class="small text-muted">{{ $transfer->toWarehouse?->address ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <th class="text-muted small" style="width: 25%;">Tanggal Pengiriman:</th>
                <td class="fw-bold">{{ optional($transfer->transfer_date)->format('d F Y') }}</td>
                <th class="text-muted small" style="width: 25%;">Diminta Oleh:</th>
                <td>{{ $transfer->requester?->name ?? 'Staff Gudang' }}</td>
            </tr>
            <tr>
                <th class="text-muted small">Status Verifikasi:</th>
                <td>{{ $transfer->approved_at ? 'Disetujui (' . $transfer->approved_at->format('d/m/Y H:i') . ')' : 'Menunggu Verifikasi' }}</td>
                <th class="text-muted small">Disetujui Oleh:</th>
                <td>{{ $transfer->approver?->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- TABEL ITEM TRANSFER --}}
    <table class="table table-bordered table-sm table-items mb-3">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">SKU Produk</th>
                <th style="width: 50%;">Nama Produk / Barang</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 10%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center fw-bold">{{ $item->product?->sku }}</td>
                <td>
                    <strong>{{ $item->product?->name }}</strong>
                </td>
                <td class="text-center fw-bold fs-6">
                    {{ (float) $item->quantity }}
                </td>
                <td class="text-center">
                    {{ $item->product?->unit?->name ?? 'PCS' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($transfer->notes)
    <div class="p-2 border rounded bg-light small mb-4">
        <strong>Catatan Pengiriman:</strong> {{ $transfer->notes }}
    </div>
    @endif

    {{-- TANDA TANGAN 3 PIHAK --}}
    <div class="row text-center mt-4">
        <div class="col-4">
            <div class="text-muted small">Pengirim (Gudang Asal):</div>
            <div class="sign-box">{{ $transfer->requester?->name ?? 'Petugas Gudang' }}</div>
            <small class="text-muted">Staff Logistik Pengirim</small>
        </div>
        <div class="col-4">
            <div class="text-muted small">Pengemudi / Ekspedisi:</div>
            <div class="sign-box">( ........................................ )</div>
            <small class="text-muted">Nama Terang &amp; No. Plat Kendaraan</small>
        </div>
        <div class="col-4">
            <div class="text-muted small">Penerima (Gudang Tujuan):</div>
            <div class="sign-box">{{ $transfer->toWarehouse?->branch?->name ? 'Staff ' . $transfer->toWarehouse->branch->name : '( ........................................ )' }}</div>
            <small class="text-muted">Cap Toko / Gudang Penerima</small>
        </div>
    </div>
</div>

</body>
</html>

