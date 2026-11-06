@extends('layouts.app')
@section('title', 'Portal Mandiri Karyawan - Profil Saya')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Portal Mandiri Karyawan (ESS)</h4>
        <span class="text-muted small">Informasi data diri, status kepegawaian, asuransi, dan keluarga</span>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('portal.profile') }}" class="btn btn-primary">Profil Saya</a>
        <a href="{{ route('portal.attendance') }}" class="btn btn-outline-primary">Absensi &amp; Lembur</a>
        <a href="{{ route('portal.payslips') }}" class="btn btn-outline-primary">Slip Gaji Saya</a>
    </div>
</div>

@if(!$employee)
<div class="alert alert-warning">
    <i class="mdi mdi-alert-circle me-1"></i>
    Akun login Anda ({{ $user->email }}) saat ini belum ditautkan dengan data master karyawan. Silakan hubungi bagian HRD atau Administrator untuk menautkan akun Anda.
</div>
@else
<div class="row g-3">
    {{-- PROFIL UTAMA --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 32px;">
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                    </span>
                </div>
                <h5 class="fw-bold mb-1">{{ $employee->full_name }}</h5>
                <span class="badge bg-info mb-2">{{ $employee->position?->name ?? 'Karyawan' }}</span>
                <div class="text-muted small">{{ $employee->department?->name ?? 'Operasional' }}</div>
                <div class="text-muted small">{{ $employee->branch?->name ?? 'Cabang Pusat' }}</div>

                <hr class="my-3">
                <div class="text-start small">
                    <div class="mb-2"><strong>Kode:</strong> {{ $employee->employee_code }}</div>
                    <div class="mb-2"><strong>NIK:</strong> {{ $employee->nik ?? '-' }}</div>
                    <div class="mb-2"><strong>Email Login:</strong> {{ $user->email }}</div>
                    <div class="mb-2"><strong>Telepon:</strong> {{ $employee->phone ?? '-' }}</div>
                    <div class="mb-2"><strong>Status Kerja:</strong> <span class="badge bg-success">{{ $employee->employment_status }}</span></div>
                    <div class="mb-2"><strong>Tgl Bergabung:</strong> {{ optional($employee->join_date)->format('d F Y') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL LENGKAP --}}
    <div class="col-md-8">
        {{-- DATA PRIBADI --}}
        <div class="card mb-3">
            <div class="card-header bg-white fw-bold">Data Pribadi</div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <label class="text-muted">Tempat, Tanggal Lahir:</label>
                        <div class="fw-bold">{{ $employee->birth_place ?? '-' }}, {{ optional($employee->birth_date)->format('d F Y') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted">Jenis Kelamin:</label>
                        <div class="fw-bold">{{ $employee->gender === 'M' ? 'Laki-laki' : ($employee->gender === 'F' ? 'Perempuan' : '-') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted">Status Pernikahan:</label>
                        <div class="fw-bold">{{ $employee->marital_status ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted">Atasan Langsung (Manager):</label>
                        <div class="fw-bold">{{ $employee->manager?->full_name ?? 'Pimpinan Cabang' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted">Alamat Domisili:</label>
                        <div class="fw-bold">{{ $employee->address ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KELUARGA --}}
        <div class="card mb-3">
            <div class="card-header bg-white fw-bold">Anggota Keluarga / Tanggungan</div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0 small">
                    <thead><tr><th>Nama</th><th>Hubungan</th><th>Tanggal Lahir</th><th>Kontak Darurat</th></tr></thead>
                    <tbody>
                        @forelse($employee->familyMembers as $fm)
                        <tr>
                            <td class="fw-bold">{{ $fm->name }}</td>
                            <td>{{ $fm->relationship }}</td>
                            <td>{{ optional($fm->birth_date)->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $fm->emergency_contact ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">Belum ada data anggota keluarga.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ASURANSI & BPJS --}}
        <div class="card">
            <div class="card-header bg-white fw-bold">Data Asuransi &amp; BPJS</div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0 small">
                    <thead><tr><th>Jenis Asuransi</th><th>Nomor Kartu / Polis</th><th>Penyedia</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($employee->insurances as $ins)
                        <tr>
                            <td class="fw-bold">{{ $ins->insurance_type }}</td>
                            <td>{{ $ins->policy_number }}</td>
                            <td>{{ $ins->provider_name }}</td>
                            <td><span class="badge bg-success">{{ $ins->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">Belum ada data asuransi terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
