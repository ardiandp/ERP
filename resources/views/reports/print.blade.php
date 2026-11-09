<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report['title'] }} - Dian Mustika ERP</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-size: 12px; color: #212529; }
        .report-container { max-width: 960px; margin: 20px auto; background: #fff; padding: 36px 40px; box-shadow: 0 0 10px rgba(0,0,0,0.08); border-radius: 4px; }
        .company-header h3 { margin: 0; font-weight: 700; color: #1f262d; }
        .table-report th { background-color: #27a9e3 !important; color: #fff !important; font-weight: 600; font-size: 12px; }
        .table-report td { vertical-align: middle; }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .report-container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; padding: 10px 15px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print py-2 bg-dark text-white mb-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 960px;">
        <span class="small"><i class="mdi mdi-printer"></i> Pratinjau Cetak Laporan: <strong>{{ $report['title'] }}</strong></span>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-primary me-2"><i class="mdi mdi-printer"></i> Cetak / Simpan PDF</button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">Tutup</button>
        </div>
    </div>
</div>

<div class="report-container">
    {{-- KOP LAPORAN --}}
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div class="company-header">
            <h3>DIAN MUSTIKA</h3>
            <p class="mb-0 text-muted small fw-bold">SPA &bull; WELLNESS &bull; BEAUTY TREATMENT</p>
            <small class="text-muted d-block">Sistem Informasi Manajemen &bull; ERP Dian Mustika</small>
        </div>
        <div class="text-end">
            <h4 class="text-uppercase fw-bold text-dark mb-1">{{ $report['title'] }}</h4>
            <div class="text-muted small">
                Periode: <strong>{{ $filters['from'] }}</strong> s/d <strong>{{ $filters['to'] }}</strong>
            </div>
            <div class="text-muted small">
                Cabang: <strong>{{ $branch ? $branch->name : 'Semua Cabang' }}</strong>
            </div>
            <div class="text-muted small">
                Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i') }}
            </div>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <table class="table table-bordered table-striped table-sm table-report mb-4">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                @foreach($report['columns'] as $column)
                <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $index => $row)
            <tr>
                <td class="text-center text-muted">{{ $index + 1 }}</td>
                @foreach($row as $value)
                <td>
                    @if(is_numeric($value))
                        {{ (float) $value == (int) $value ? number_format((float) $value, 0, ',', '.') : number_format((float) $value, 2, ',', '.') }}
                    @else
                        {{ $value }}
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($report['columns']) + 1 }}" class="text-center py-3 text-muted">
                    Tidak ada data laporan untuk kriteria filter yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN & VALIDASI --}}
    <div class="row text-center mt-5 pt-3">
        <div class="col-6">
            <div class="text-muted small">Dibuat Oleh:</div>
            <div style="border-top: 1px dashed #6c757d; margin-top: 55px; padding-top: 6px; font-weight: 600;">
                {{ auth()->user()?->name ?? 'Staff Operasional' }}
            </div>
            <small class="text-muted">Petugas Administrasi / Kasir</small>
        </div>
        <div class="col-6">
            <div class="text-muted small">Mengetahui / Menyetujui:</div>
            <div style="border-top: 1px dashed #6c757d; margin-top: 55px; padding-top: 6px; font-weight: 600;">
                Dian Mustika
            </div>
            <small class="text-muted">Branch Manager / Direktur</small>
        </div>
    </div>
</div>

</body>
</html>
