@extends('layouts.app')
@section('title', 'Portal Mandiri Karyawan - Absensi & Lembur')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Absensi &amp; Pengajuan Lembur Saya</h4>
        <span class="text-muted small">Riwayat kehadiran bulan ini dan form pengajuan lembur mandiri</span>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('portal.profile') }}" class="btn btn-outline-primary">Profil Saya</a>
        <a href="{{ route('portal.attendance') }}" class="btn btn-primary">Absensi &amp; Lembur</a>
        <a href="{{ route('portal.payslips') }}" class="btn btn-outline-primary">Slip Gaji Saya</a>
    </div>
</div>

{{-- METRIK BULAN INI --}}
<div class="row g-2 mb-3">
    <div class="col-md-3 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Hadir Bulan Ini</span>
            <h4 class="fw-bold mb-0 text-success">{{ $stats['present'] }} Hari</h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Terlambat</span>
            <h4 class="fw-bold mb-0 text-warning">{{ $stats['late'] }} Hari</h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Izin / Sakit</span>
            <h4 class="fw-bold mb-0 text-info">{{ $stats['leave'] }} Hari</h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Lembur Disetujui</span>
            <h4 class="fw-bold mb-0 text-primary">{{ number_format($stats['overtime_hours'], 1) }} Jam</h4>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- RIWAYAT ABSENSI --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">Riwayat Kehadiran ({{ now()->translatedFormat('F Y') }})</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0 small">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $att)
                            <tr>
                                <td>{{ optional($att->attendance_date)->format('d/m/Y') }}</td>
                                <td class="fw-bold">{{ $att->check_in_time ?? '-' }}</td>
                                <td>{{ $att->check_out_time ?? '-' }}</td>
                                <td>
                                    @php
                                        $cls = match($att->status) {
                                            'PRESENT' => 'bg-success',
                                            'LATE' => 'bg-warning text-dark',
                                            'ABSENT' => 'bg-danger',
                                            default => 'bg-info',
                                        };
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ $att->status }}</span>
                                </td>
                                <td>{{ $att->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada catatan absensi bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PENGAJUAN LEMBUR --}}
    <div class="col-lg-5">
        {{-- FORM AJUKAN LEMBUR --}}
        <div class="card mb-3">
            <div class="card-header bg-white fw-bold">Form Pengajuan Lembur</div>
            <div class="card-body">
                <form method="POST" action="{{ route('portal.overtime.submit') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" name="overtime_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control form-control-sm" value="18:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control form-control-sm" value="20:00" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="mdi mdi-send me-1"></i> Ajukan Lembur
                    </button>
                </form>
            </div>
        </div>

        {{-- RIWAYAT STATUS LEMBUR --}}
        <div class="card">
            <div class="card-header bg-white fw-bold">Status Pengajuan Lembur</div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped align-middle mb-0 small">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Durasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $ot)
                        <tr>
                            <td>{{ optional($ot->overtime_date)->format('d/m') }}</td>
                            <td>{{ $ot->start_time }} - {{ $ot->end_time }}</td>
                            <td>{{ number_format($ot->hours, 1) }} jam</td>
                            <td>
                                @php
                                    $stClass = match($ot->status) {
                                        'APPROVED' => 'bg-success',
                                        'REJECTED' => 'bg-danger',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $stClass }}">{{ $ot->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">Belum ada pengajuan lembur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

