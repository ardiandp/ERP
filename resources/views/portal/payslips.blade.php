@extends('layouts.app')
@section('title', 'Portal Mandiri Karyawan - Slip Gaji')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Slip Gaji Karyawan</h4>
        <span class="text-muted small">Daftar slip gaji resmi yang telah dipublikasikan dan siap diunduh</span>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('portal.profile') }}" class="btn btn-outline-primary">Profil Saya</a>
        <a href="{{ route('portal.attendance') }}" class="btn btn-outline-primary">Absensi &amp; Lembur</a>
        <a href="{{ route('portal.payslips') }}" class="btn btn-primary">Slip Gaji Saya</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle datatable-table">
                <thead>
                    <tr>
                        <th>No. Slip Gaji</th>
                        <th>Periode Gaji</th>
                        <th>Tanggal Terbit</th>
                        <th class="text-end">Gaji Kotor</th>
                        <th class="text-end">Total Potongan</th>
                        <th class="text-end">Gaji Bersih (Take Home Pay)</th>
                        <th>Kode Verifikasi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payslips as $ps)
                    <tr>
                        <td class="fw-bold">{{ $ps->payslip_number }}</td>
                        <td>{{ $ps->payroll?->period?->name ?? '-' }}</td>
                        <td>{{ optional($ps->issue_date)->format('d/m/Y') }}</td>
                        <td class="text-end">Rp {{ number_format($ps->gross_salary, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">- Rp {{ number_format($ps->total_deduction, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-success fs-6">Rp {{ number_format($ps->net_salary, 0, ',', '.') }}</td>
                        <td><code>{{ $ps->verification_code ?? '-' }}</code></td>
                        <td class="text-center">
                            <a href="{{ route('payslips.print', $ps) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-printer me-1"></i> Cetak / Unduh PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada slip gaji yang dipublikasikan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

