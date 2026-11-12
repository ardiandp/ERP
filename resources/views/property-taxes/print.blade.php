<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PBB - {{ $tax->nop }} - Dian Mustika ERP</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-size: 13px; color: #212529; }
        .tax-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 36px 40px; box-shadow: 0 0 10px rgba(0,0,0,0.08); border-radius: 4px; }
        .company-header h3 { margin: 0; font-weight: 700; color: #1f262d; }
        .table-tax th { background-color: #f1f3f5 !important; font-weight: 600; font-size: 12px; }
        .table-tax td { vertical-align: middle; }
        .sign-box { border-top: 1px dashed #6c757d; margin-top: 55px; padding-top: 6px; font-weight: 600; font-size: 12px; }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .tax-container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 15px 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print py-2 bg-dark text-white mb-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 800px;">
        <span class="small"><i class="mdi mdi-printer"></i> Pratinjau Dokumen PBB: <strong>NOP {{ $tax->nop }}</strong></span>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-primary me-2"><i class="mdi mdi-printer"></i> Cetak Dokumen</button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">Tutup</button>
        </div>
    </div>
</div>

<div class="tax-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div class="company-header">
            <h3>DIAN MUSTIKA</h3>
            <p class="mb-0 text-muted small fw-bold">SPA &bull; WELLNESS &bull; BEAUTY TREATMENT</p>
            <small class="text-muted d-block">Manajemen Aset &amp; Pajak Bumi Bangunan (PBB)</small>
        </div>
        <div class="text-end">
            <h5 class="text-uppercase fw-bold text-dark mb-0">LEMBAR REKAPITULASI SPPT PBB</h5>
            <small class="text-muted d-block font-monospace">TAHUN PAJAK: {{ $tax->tahun }}</small>
            <small class="text-muted d-block mt-1">Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }}</small>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="d-flex justify-content-between align-items-center p-2 mb-3 rounded border bg-light">
        <div>
            <span class="small text-muted">Nomor Objek Pajak (NOP):</span>
            <div class="fw-bold font-monospace fs-6 text-primary">{{ $tax->nop }}</div>
        </div>
        <div class="text-end">
            <span class="small text-muted d-block">Status Pembayaran SPPT:</span>
            @if($tax->status_bayar === 'LUNAS')
            <span class="badge bg-success fs-6"><i class="mdi mdi-check-circle"></i> LUNAS</span>
            @elseif($tax->status_bayar === 'KURANG_BAYAR')
            <span class="badge bg-warning text-dark fs-6"><i class="mdi mdi-alert"></i> KURANG BAYAR</span>
            @else
            <span class="badge bg-danger fs-6"><i class="mdi mdi-close-circle"></i> BELUM LUNAS</span>
            @endif
        </div>
    </div>

    <!-- 1. Data Objek Pajak & Wajib Pajak -->
    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">1. Data Objek &amp; Wajib Pajak</h6>
    <table class="table table-sm table-bordered table-tax mb-3">
        <tr>
            <th style="width: 25%;">Alamat Objek Pajak</th>
            <td style="width: 75%;" colspan="3">{{ $tax->alamat_op }}</td>
        </tr>
        <tr>
            <th>Kelurahan / Desa</th>
            <td>{{ $tax->desa ?: '-' }}</td>
            <th style="width: 20%;">Kecamatan</th>
            <td>{{ $tax->kecamatan ?: '-' }}</td>
        </tr>
        <tr>
            <th>Kota / Kabupaten</th>
            <td>{{ $tax->kota_kab ?: '-' }}</td>
            <th>Cabang Terkait</th>
            <td>{{ $tax->branch?->name ?: 'Pusat / Non-Cabang' }}</td>
        </tr>
        <tr>
            <th>Luas Bumi / Tanah</th>
            <td>{{ $tax->luas_bumi ? number_format($tax->luas_bumi, 2, ',', '.') : '-' }} m²</td>
            <th>Luas Bangunan</th>
            <td>{{ $tax->luas_bangunan ? number_format($tax->luas_bangunan, 2, ',', '.') : '-' }} m²</td>
        </tr>
        <tr>
            <th>Nama Wajib Pajak (WP)</th>
            <td class="fw-bold">{{ $tax->nama_wp }}</td>
            <th>NPWP</th>
            <td class="font-monospace">{{ $tax->npwp ?: '-' }}</td>
        </tr>
    </table>

    <!-- 2. Rincian Pajak & Pembayaran -->
    <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">2. Ketetapan Pajak &amp; Realisasi Pembayaran</h6>
    <table class="table table-bordered table-sm table-tax mb-4">
        <thead>
            <tr>
                <th style="width: 45%;">Uraian Komponen</th>
                <th style="width: 25%;" class="text-center">Tanggal Transaksi</th>
                <th style="width: 30%;" class="text-end">Jumlah Nominal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Ketetapan PBB Terutang (Tahun {{ $tax->tahun }})</strong></td>
                <td class="text-center">-</td>
                <td class="text-end fw-bold">Rp {{ number_format($tax->pajak ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="table-success">
                <td><strong>Realisasi Pembayaran (SPPT Yang Sudah Dibayar)</strong></td>
                <td class="text-center fw-semibold">{{ $tax->tgl_bayar ? $tax->tgl_bayar->format('d/m/Y') : '-' }}</td>
                <td class="text-end fw-bold text-success">Rp {{ number_format($tax->sppt_yg_dibayar ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="{{ $tax->sisa_pajak > 0 ? 'table-danger' : 'table-light' }}">
                <th colspan="2" class="text-end fw-bold">SISA TAGIHAN / TUNGGAKAN:</th>
                <td class="text-end fw-bold fs-6 {{ $tax->sisa_pajak > 0 ? 'text-danger' : 'text-dark' }}">
                    Rp {{ number_format($tax->sisa_pajak, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    @if($tax->keterangan)
    <div class="mb-4 p-2 bg-light border rounded small">
        <strong>Catatan Khusus:</strong> {{ $tax->keterangan }}
    </div>
    @endif

    <!-- Signatures -->
    <div class="row text-center mt-5">
        <div class="col-6">
            <div class="text-muted small">Petugas Administrasi Pajak / Keuangan:</div>
            <div class="sign-box">{{ $tax->creator?->name ?? 'Staf Keuangan' }}</div>
            <small class="text-muted">Staff Finance &amp; Tax</small>
        </div>
        <div class="col-6">
            <div class="text-muted small">Mengetahui &amp; Menyetujui:</div>
            <div class="sign-box">Dian Mustika</div>
            <small class="text-muted">Finance Manager / Direktur</small>
        </div>
    </div>
</div>

</body>
</html>
