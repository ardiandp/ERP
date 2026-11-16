@extends('layouts.app')
@section('title', 'Rekapitulasi BPJS Karyawan')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-7">
        <h4 class="mb-0 text-dark fw-bold"><i class="mdi mdi-shield-account text-primary"></i> Rekapitulasi BPJS Karyawan</h4>
        <small class="text-muted">Monitoring kepesertaan BPJS Kesehatan &amp; BPJS Ketenagakerjaan seluruh staf</small>
    </div>
    <div class="col-md-5 text-md-end mt-2 mt-md-0">
        <a href="{{ route('hr.bpjs.export', request()->query()) }}" class="btn btn-success btn-sm shadow-sm me-1" title="Unduh rekap BPJS ke format CSV">
            <i class="mdi mdi-file-excel"></i> Export CSV
        </a>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm" title="Kembali ke Daftar Karyawan">
            <i class="mdi mdi-account-multiple"></i> Data Karyawan
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="mdi mdi-alert-circle me-1"></i> {{ $errors->first() }}
    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<!-- Summary KPI Cards -->
<div class="row g-2 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-primary"><i class="mdi mdi-account-multiple mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Karyawan Aktif</small>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($metrics['total_active']) }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Total staf operasional aktif</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-success"><i class="mdi mdi-heart-pulse mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">BPJS Kesehatan</small>
                        <h4 class="mb-0 fw-bold text-success">{{ number_format($metrics['kes_count']) }}</h4>
                        <small class="text-muted" style="font-size: 11px;">
                            {{ $metrics['total_active'] > 0 ? round(($metrics['kes_count'] / $metrics['total_active']) * 100) : 0 }}% terdaftar aktif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-info"><i class="mdi mdi-shield-check mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">BPJS Ketenagakerjaan</small>
                        <h4 class="mb-0 fw-bold text-info">{{ number_format($metrics['tk_count']) }}</h4>
                        <small class="text-muted" style="font-size: 11px;">
                            {{ $metrics['total_active'] > 0 ? round(($metrics['tk_count'] / $metrics['total_active']) * 100) : 0 }}% terdaftar aktif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-warning"><i class="mdi mdi-account-alert mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Belum Terdaftar</small>
                        <h4 class="mb-0 fw-bold text-warning">{{ number_format($metrics['unregistered_count']) }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Belum memiliki BPJS apapun</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('hr.bpjs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Status Kepesertaan</label>
                <select name="bpjs_status" class="form-control form-control-sm custom-select">
                    <option value="ALL" @selected($filters['bpjs_status'] === 'ALL')>Semua Status BPJS</option>
                    <option value="BOTH" @selected($filters['bpjs_status'] === 'BOTH')>Lengkap (Kes &amp; TK)</option>
                    <option value="KES_ONLY" @selected($filters['bpjs_status'] === 'KES_ONLY')>Hanya BPJS Kesehatan</option>
                    <option value="TK_ONLY" @selected($filters['bpjs_status'] === 'TK_ONLY')>Hanya BPJS Ketenagakerjaan</option>
                    <option value="NONE" @selected($filters['bpjs_status'] === 'NONE')>Belum Terdaftar BPJS</option>
                    <option value="HAS_ANY" @selected($filters['bpjs_status'] === 'HAS_ANY')>Memiliki Minimal 1 BPJS</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Cabang / Unit</label>
                <select name="branch_id" class="form-control form-control-sm custom-select">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($filters['branch_id'] === $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Status Karyawan</label>
                <select name="status" class="form-control form-control-sm custom-select">
                    <option value="ACTIVE" @selected($filters['status'] === 'ACTIVE')>Karyawan Aktif</option>
                    <option value="INACTIVE" @selected($filters['status'] === 'INACTIVE')>Karyawan Nonaktif</option>
                    <option value="ALL" @selected($filters['status'] === 'ALL')>Semua Karyawan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Cari Karyawan / No. BPJS</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Nama, NIK, No. Kartu BPJS Kes, No. KPJ..." value="{{ $filters['search'] }}">
                    @if(request('search'))
                    <div class="input-group-append">
                        <a href="{{ route('hr.bpjs.index', request()->except('search', 'page')) }}" class="btn btn-outline-secondary" title="Hapus pencarian"><i class="mdi mdi-close"></i></a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Terapkan Filter"><i class="mdi mdi-magnify"></i> Cari</button>
                @if(request()->anyFilled(['bpjs_status', 'branch_id', 'search']) || (request()->filled('status') && request('status') !== 'ACTIVE'))
                <a href="{{ route('hr.bpjs.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Semua Filter"><i class="mdi mdi-refresh"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border shadow-sm">
    <div class="card-header bg-white py-2 px-3 border-bottom">
        <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0 d-flex align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold text-dark me-1">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i> Daftar Rekap BPJS Karyawan
                </h6>
                @if(request('search'))
                    <span class="badge bg-primary text-white">
                        <i class="mdi mdi-database-search"></i> Database: "{{ request('search') }}"
                        <a href="{{ route('hr.bpjs.index', request()->except('search', 'page')) }}" class="text-white ms-1 text-decoration-none" title="Hapus filter">&times;</a>
                    </span>
                @endif
                <span id="quickSearchMatchCount" class="badge bg-light text-muted border" style="display: none;"></span>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-inline-flex align-items-center" style="max-width: 320px; width: 100%;">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-magnify text-muted"></i></span>
                        </div>
                        <input type="text" id="quickTableSearch" class="form-control border-left-0" placeholder="Filter cepat di tabel ini..." autocomplete="off">
                        <div class="input-group-append" id="quickSearchClearBtn" style="display: none;">
                            <button class="btn btn-outline-secondary" type="button" title="Bersihkan filter"><i class="mdi mdi-close"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="bpjsTable" class="table table-hover table-striped align-middle mb-0 no-datatable" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">No</th>
                        <th>Karyawan &amp; Jabatan</th>
                        <th>Cabang</th>
                        <th>BPJS Kesehatan</th>
                        <th>BPJS Ketenagakerjaan</th>
                        <th class="text-end">Iuran Karyawan</th>
                        <th class="text-end">Subsidi Perusahaan</th>
                        <th style="width: 110px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $idx => $emp)
                    @php
                        $kes = $emp->bpjs_kes;
                        $tk = $emp->bpjs_tk;
                        $hasKes = $kes && $kes->status === 'ACTIVE' && !empty($kes->membership_number);
                        $hasTk = $tk && $tk->status === 'ACTIVE' && !empty($tk->membership_number);
                        $empTotal = ($hasKes ? (float)$kes->employee_contribution : 0) + ($hasTk ? (float)$tk->employee_contribution : 0);
                        $compTotal = ($hasKes ? (float)$kes->company_contribution : 0) + ($hasTk ? (float)$tk->company_contribution : 0);
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $employees->firstItem() + $idx }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $emp->full_name }}</div>
                            <div class="small text-muted">
                                <span class="font-monospace text-secondary me-2">{{ $emp->employee_code }}</span>
                                @if($emp->nik)
                                <span>NIK: {{ $emp->nik }}</span>
                                @endif
                            </div>
                            <div class="small text-primary font-weight-semibold">{{ $emp->position?->name ?? 'Staf' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $emp->branch?->name ?? '-' }}</span>
                        </td>
                        <td>
                            @if($hasKes)
                                <div class="font-monospace font-weight-bold text-success" style="font-size: 12px;">
                                    <i class="mdi mdi-credit-card-outline"></i> {{ $kes->membership_number }}
                                </div>
                                <div class="small text-muted">
                                    <span class="badge bg-success text-white py-0 px-1" style="font-size: 10px;">Kelas {{ $kes->insurance_class ?: '1' }}</span>
                                    <span class="text-muted ms-1">Mulai: {{ $kes->start_date ? $kes->start_date->format('d/m/Y') : '-' }}</span>
                                </div>
                            @else
                                <span class="badge bg-secondary text-white" style="font-size: 11px;">Belum Terdaftar</span>
                            @endif
                        </td>
                        <td>
                            @if($hasTk)
                                <div class="font-monospace font-weight-bold text-info" style="font-size: 12px;">
                                    <i class="mdi mdi-card-bulleted-outline"></i> {{ $tk->membership_number }}
                                </div>
                                <div class="small text-muted">
                                    <span class="badge bg-info text-dark py-0 px-1" style="font-size: 10px;">KPJ Aktif</span>
                                    <span class="text-muted ms-1">Mulai: {{ $tk->start_date ? $tk->start_date->format('d/m/Y') : '-' }}</span>
                                </div>
                            @else
                                <span class="badge bg-secondary text-white" style="font-size: 11px;">Belum Terdaftar</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold {{ $empTotal > 0 ? 'text-dark' : 'text-muted' }}">
                            Rp {{ number_format($empTotal, 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold {{ $compTotal > 0 ? 'text-primary' : 'text-muted' }}">
                            Rp {{ number_format($compTotal, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-sm btn-primary py-1 px-2 btn-manage-bpjs shadow-sm" 
                                    style="font-size: 11px;"
                                    data-id="{{ $emp->id }}"
                                    data-name="{{ $emp->full_name }}"
                                    data-code="{{ $emp->employee_code }}"
                                    data-branch="{{ $emp->branch?->name ?? '-' }}"
                                    data-action="{{ route('hr.bpjs.update', $emp) }}"
                                    data-kes-number="{{ $kes?->membership_number ?? '' }}"
                                    data-kes-class="{{ $kes?->insurance_class ?? '1' }}"
                                    data-kes-start="{{ $kes?->start_date ? $kes->start_date->format('Y-m-d') : '' }}"
                                    data-kes-emp="{{ $kes?->employee_contribution ?? 0 }}"
                                    data-kes-comp="{{ $kes?->company_contribution ?? 0 }}"
                                    data-kes-status="{{ $kes?->status ?? 'ACTIVE' }}"
                                    data-tk-number="{{ $tk?->membership_number ?? '' }}"
                                    data-tk-start="{{ $tk?->start_date ? $tk->start_date->format('Y-m-d') : '' }}"
                                    data-tk-emp="{{ $tk?->employee_contribution ?? 0 }}"
                                    data-tk-comp="{{ $tk?->company_contribution ?? 0 }}"
                                    data-tk-status="{{ $tk?->status ?? 'ACTIVE' }}"
                                    title="Kelola data nomor BPJS & iuran">
                                <i class="mdi mdi-pencil"></i> Kelola BPJS
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="mdi mdi-account-search-outline mdi-36px d-block mb-1 text-muted"></i>
                            Tidak ada data karyawan yang cocok dengan kriteria filter.
                        </td>
                    </tr>
                    @endforelse
                    <tr id="noFilterMatchRow" class="no-filter-row" style="display: none;">
                        <td colspan="8" class="text-center py-4 bg-light text-muted">
                            <i class="mdi mdi-file-search-outline mdi-36px d-block mb-1 text-primary"></i>
                            <span>Tidak ada karyawan yang cocok dengan kata kunci "<strong id="noFilterQueryText"></strong>" di halaman ini.</span>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-primary" id="btnSearchAllPages">
                                    <i class="mdi mdi-database-search"></i> Cari di Seluruh Database
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-white border-top py-2 px-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-1 mb-md-0">
                Menampilkan <strong>{{ $employees->firstItem() }}</strong> s/d <strong>{{ $employees->lastItem() }}</strong> dari <strong>{{ $employees->total() }}</strong> total karyawan
            </small>
            <div class="d-flex align-items-center">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Kelola BPJS Karyawan -->
<div class="modal fade" id="modalManageBpjs" tabindex="-1" role="dialog" aria-labelledby="modalManageBpjsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="formManageBpjs" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="modalManageBpjsLabel">
                        <i class="mdi mdi-shield-account"></i> Kelola Data BPJS: <span id="modalEmployeeName"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border py-2 px-3 small mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="mdi mdi-account-badge text-primary"></i> 
                                <strong id="modalEmpCode"></strong> &bull; <span id="modalEmpBranch"></span>
                            </div>
                            <span class="text-muted small">Kosongkan nomor kartu jika staf belum memiliki BPJS terkait.</span>
                        </div>
                    </div>

                    <div class="row">
                        <!-- BPJS Kesehatan Column -->
                        <div class="col-md-6 border-right">
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom">
                                <i class="mdi mdi-heart-pulse text-success me-2 fs-4"></i>
                                <h6 class="mb-0 fw-bold text-success">BPJS Kesehatan</h6>
                            </div>

                            <div class="form-group mb-2">
                                <label for="bpjs_kes_number" class="form-label font-weight-bold small">Nomor Kartu Peserta</label>
                                <input type="text" name="bpjs_kes_number" id="bpjs_kes_number" class="form-control form-control-sm font-monospace" placeholder="Contoh: 0001234567890">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label for="bpjs_kes_class" class="form-label font-weight-bold small">Kelas Rawat</label>
                                    <select name="bpjs_kes_class" id="bpjs_kes_class" class="form-control form-control-sm custom-select">
                                        <option value="1">Kelas 1</option>
                                        <option value="2">Kelas 2</option>
                                        <option value="3">Kelas 3</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="bpjs_kes_status" class="form-label font-weight-bold small">Status Kartu</label>
                                    <select name="bpjs_kes_status" id="bpjs_kes_status" class="form-control form-control-sm custom-select">
                                        <option value="ACTIVE">AKTIF</option>
                                        <option value="INACTIVE">NONAKTIF</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label for="bpjs_kes_start_date" class="form-label font-weight-bold small">Tanggal Mulai Berlaku</label>
                                <input type="date" name="bpjs_kes_start_date" id="bpjs_kes_start_date" class="form-control form-control-sm">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label for="bpjs_kes_emp_contrib" class="form-label font-weight-bold small">Iuran Karyawan (Rp)</label>
                                    <input type="number" step="1000" name="bpjs_kes_emp_contrib" id="bpjs_kes_emp_contrib" class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-6">
                                    <label for="bpjs_kes_comp_contrib" class="form-label font-weight-bold small">Subsidi Kantor (Rp)</label>
                                    <input type="number" step="1000" name="bpjs_kes_comp_contrib" id="bpjs_kes_comp_contrib" class="form-control form-control-sm" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- BPJS Ketenagakerjaan Column -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom">
                                <i class="mdi mdi-shield-check text-info me-2 fs-4"></i>
                                <h6 class="mb-0 fw-bold text-info">BPJS Ketenagakerjaan</h6>
                            </div>

                            <div class="form-group mb-2">
                                <label for="bpjs_tk_number" class="form-label font-weight-bold small">Nomor KPJ (Kartu Peserta)</label>
                                <input type="text" name="bpjs_tk_number" id="bpjs_tk_number" class="form-control form-control-sm font-monospace" placeholder="Contoh: 22012345678">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label for="bpjs_tk_status" class="form-label font-weight-bold small">Status Kartu</label>
                                    <select name="bpjs_tk_status" id="bpjs_tk_status" class="form-control form-control-sm custom-select">
                                        <option value="ACTIVE">AKTIF</option>
                                        <option value="INACTIVE">NONAKTIF</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="bpjs_tk_start_date" class="form-label font-weight-bold small">Mulai Berlaku</label>
                                    <input type="date" name="bpjs_tk_start_date" id="bpjs_tk_start_date" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label for="bpjs_tk_emp_contrib" class="form-label font-weight-bold small">Iuran Karyawan (Rp)</label>
                                    <input type="number" step="1000" name="bpjs_tk_emp_contrib" id="bpjs_tk_emp_contrib" class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-6">
                                    <label for="bpjs_tk_comp_contrib" class="form-label font-weight-bold small">Subsidi Kantor (Rp)</label>
                                    <input type="number" step="1000" name="bpjs_tk_comp_contrib" id="bpjs_tk_comp_contrib" class="form-control form-control-sm" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="mdi mdi-check"></i> Simpan Data BPJS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Open modal and populate fields
    $(document).on('click', '.btn-manage-bpjs', function(e) {
        e.preventDefault();
        var $btn = $(this);

        $('#modalEmployeeName').text($btn.data('name'));
        $('#modalEmpCode').text($btn.data('code'));
        $('#modalEmpBranch').text($btn.data('branch'));
        $('#formManageBpjs').attr('action', $btn.data('action'));

        // BPJS Kes
        $('#bpjs_kes_number').val($btn.data('kes-number'));
        $('#bpjs_kes_class').val($btn.data('kes-class') || '1');
        $('#bpjs_kes_start_date').val($btn.data('kes-start'));
        $('#bpjs_kes_emp_contrib').val($btn.data('kes-emp'));
        $('#bpjs_kes_comp_contrib').val($btn.data('kes-comp'));
        $('#bpjs_kes_status').val($btn.data('kes-status') || 'ACTIVE');

        // BPJS TK
        $('#bpjs_tk_number').val($btn.data('tk-number'));
        $('#bpjs_tk_start_date').val($btn.data('tk-start'));
        $('#bpjs_tk_emp_contrib').val($btn.data('tk-emp'));
        $('#bpjs_tk_comp_contrib').val($btn.data('tk-comp'));
        $('#bpjs_tk_status').val($btn.data('tk-status') || 'ACTIVE');

        $('#modalManageBpjs').modal('show');
    });

    // Quick Live Table Filter
    var $tableRows = $('#bpjsTable tbody tr:not(.no-filter-row)');
    $('#quickTableSearch').on('keyup input', function() {
        var query = $(this).val().toLowerCase().trim();
        var matches = 0;

        if (query.length > 0) {
            $('#quickSearchClearBtn').show();
            $tableRows.each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(query) !== -1) {
                    $(this).show();
                    matches++;
                } else {
                    $(this).hide();
                }
            });

            $('#quickSearchMatchCount').show().text(matches + ' baris cocok di halaman ini');

            if (matches === 0) {
                $('#noFilterQueryText').text(query);
                $('#noFilterMatchRow').show();
            } else {
                $('#noFilterMatchRow').hide();
            }
        } else {
            $('#quickSearchClearBtn').hide();
            $('#quickSearchMatchCount').hide();
            $('#noFilterMatchRow').hide();
            $tableRows.show();
        }
    });

    $('#quickSearchClearBtn button').on('click', function() {
        $('#quickTableSearch').val('').trigger('input').focus();
    });

    // Pressing Enter submits to database search
    $('#quickTableSearch').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            var val = $(this).val().trim();
            $('input[name="search"]').val(val);
            $('input[name="search"]').closest('form').submit();
        }
    });

    // Search all pages button
    $(document).on('click', '#btnSearchAllPages', function(e) {
        e.preventDefault();
        var val = $('#quickTableSearch').val().trim();
        $('input[name="search"]').val(val);
        $('input[name="search"]').closest('form').submit();
    });
});
</script>
@endpush

