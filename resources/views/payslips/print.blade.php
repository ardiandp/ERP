<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payslip->payslip_number }}</title>
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .payslip-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 45px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .company-header {
            border-bottom: 2px solid #27a9e3;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-title {
            font-size: 20px;
            font-weight: 700;
            color: #27a9e3;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .company-subtitle {
            font-size: 12px;
            color: #666;
            margin: 0;
        }
        .slip-title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 15px 0 20px 0;
            color: #333;
        }
        .meta-table td {
            padding: 3px 8px;
            font-size: 13px;
        }
        .items-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .items-table th {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
            padding: 8px 10px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f1f1;
            font-size: 13px;
        }
        .total-box {
            background-color: #e8f4f8;
            border-left: 4px solid #27a9e3;
            padding: 12px 15px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .payslip-container {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print bg-dark text-white p-2 text-center">
        <button onclick="window.print()" class="btn btn-sm btn-success me-2">
            <i class="mdi mdi-printer"></i> Cetak Dokumen / PDF
        </button>
        <a href="{{ route('payslips.show', $payslip) }}" class="btn btn-sm btn-outline-light">
            Kembali ke Detail Slip
        </a>
    </div>

    <div class="payslip-container">
        {{-- HEADER KLINIK --}}
        <div class="company-header d-flex justify-content-between align-items-center">
            <div>
                <div class="company-title">Dian Mustika</div>
                <div class="company-subtitle">
                    Klinik Perawatan &amp; Salon Kecantikan Muslimah<br>
                    Cabang: {{ $payslip->employee?->branch?->name ?? 'Pusat' }} — {{ $payslip->employee?->branch?->address ?? 'Indonesia' }}
                </div>
            </div>
            <div class="text-end">
                <span class="badge {{ $payslip->status === 'PUBLISHED' ? 'bg-success' : 'bg-warning' }} fs-6">
                    {{ $payslip->status }}
                </span>
                <div class="small text-muted mt-1">No: {{ $payslip->payslip_number }}</div>
            </div>
        </div>

        <div class="slip-title">Slip Pembayaran Gaji Karyawan</div>

        {{-- METADATA --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <table class="meta-table">
                    <tr><td class="text-muted" style="width: 120px;">Nama Karyawan</td><td>: <strong>{{ $payslip->employee?->full_name }}</strong></td></tr>
                    <tr><td class="text-muted">NIK / Kode</td><td>: {{ $payslip->employee?->nik ?? $payslip->employee?->employee_code }}</td></tr>
                    <tr><td class="text-muted">Departemen</td><td>: {{ $payslip->employee?->department?->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Jabatan</td><td>: {{ $payslip->employee?->position?->name ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-6">
                <table class="meta-table">
                    <tr><td class="text-muted" style="width: 120px;">Periode Gaji</td><td>: <strong>{{ $payslip->payroll?->period?->name }}</strong></td></tr>
                    <tr><td class="text-muted">Tanggal Cetak</td><td>: {{ now()->translatedFormat('d F Y') }}</td></tr>
                    <tr><td class="text-muted">Status Kerja</td><td>: {{ $payslip->employee?->employment_status ?? 'TETAP' }}</td></tr>
                    <tr><td class="text-muted">Kode Verifikasi</td><td>: <code>{{ $payslip->verification_code ?? '-' }}</code></td></tr>
                </table>
            </div>
        </div>

        {{-- ITEM RINCIAN GAJI --}}
        <div class="row g-3">
            {{-- PENGHASILAN --}}
            <div class="col-6">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Penghasilan (Earnings)</th>
                            <th class="text-end">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $earnings = $payslip->items->filter(fn($i) => in_array($i->type, ['ALLOWANCE', 'COMMISSION', 'OVERTIME', 'BONUS']) || $i->type === 'EARNING');
                            $deductions = $payslip->items->filter(fn($i) => in_array($i->type, ['DEDUCTION', 'LOAN', 'PENALTY', 'BPJS']));
                        @endphp
                        @forelse($earnings as $item)
                        <tr>
                            <td>{{ $item->component_name }}</td>
                            <td class="text-end">{{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td>Gaji Pokok &amp; Tunjangan</td>
                            <td class="text-end">{{ number_format($payslip->gross_salary, 0, ',', '.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="fw-bold">Total Penghasilan Kotor</th>
                            <th class="text-end fw-bold text-primary">Rp {{ number_format($payslip->gross_salary, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- POTONGAN --}}
            <div class="col-6">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Potongan (Deductions)</th>
                            <th class="text-end">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductions as $item)
                        <tr>
                            <td>{{ $item->component_name }}</td>
                            <td class="text-end text-danger">{{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-muted small text-center py-2">Tidak ada potongan</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="fw-bold">Total Potongan</th>
                            <th class="text-end fw-bold text-danger">Rp {{ number_format($payslip->total_deduction, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- TAKE HOME PAY BOX --}}
        <div class="total-box">
            <div>
                <span class="text-muted text-uppercase small d-block">Gaji Bersih Diterima (Take Home Pay)</span>
                <span class="fs-4 fw-bold text-dark">Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}</span>
            </div>
            <div class="text-end">
                <span class="badge bg-primary">DIAN MUSTIKA OFFICIAL</span>
            </div>
        </div>

        {{-- TANDA TANGAN --}}
        <div class="signature-section d-flex justify-content-between">
            <div class="signature-box">
                <div class="small text-muted">Diterima Oleh Karyawan,</div>
                <div class="signature-line"></div>
                <div class="fw-bold small">{{ $payslip->employee?->full_name }}</div>
            </div>
            <div class="signature-box">
                <div class="small text-muted">Disetujui Oleh Manajemen / HRD,</div>
                <div class="signature-line"></div>
                <div class="fw-bold small">HRD Dian Mustika</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top">
            Dokumen ini dicetak secara otomatis melalui ERP Dian Mustika dan merupakan bukti pembayaran resmi.
        </div>
    </div>
</body>
</html>
