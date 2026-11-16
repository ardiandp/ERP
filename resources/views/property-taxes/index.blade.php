@extends('layouts.app')
@section('title', 'Pajak Bumi & Bangunan (PBB)')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-0 text-dark fw-bold"><i class="mdi mdi-home-currency-usd text-primary"></i> Data Pajak Bumi &amp; Bangunan (PBB)</h4>
        <small class="text-muted">Pengelolaan dan rekonsiliasi SPPT PBB aset &amp; properti operasional</small>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="{{ route('property-taxes.create') }}" class="btn btn-primary btn-sm me-1 shadow-sm">
            <i class="mdi mdi-plus-circle"></i> Tambah PBB
        </a>
        <button type="button" id="btnImportCsv" class="btn btn-info btn-sm text-white me-1 shadow-sm" data-toggle="modal" data-target="#modalImportCsv" data-bs-toggle="modal" data-bs-target="#modalImportCsv">
            <i class="mdi mdi-upload"></i> Import CSV
        </button>
        <a href="{{ route('property-taxes.template') }}" class="btn btn-outline-secondary btn-sm me-1 shadow-sm" title="Unduh format template CSV">
            <i class="mdi mdi-download"></i> Template CSV
        </a>
        <a href="{{ route('property-taxes.export', request()->query()) }}" class="btn btn-success btn-sm shadow-sm" title="Ekspor data PBB ke format CSV">
            <i class="mdi mdi-file-excel"></i> Export CSV
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-3 g-2">
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-primary"><i class="mdi mdi-home-city mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Total Objek (NOP)</small>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($metrics['total_count']) }}</h4>
                        <small class="text-muted" style="font-size: 11px;">{{ $metrics['total_lunas'] }} Lunas &bull; {{ $metrics['total_belum_lunas'] }} Belum</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-info"><i class="mdi mdi-file-document-box mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Total Pajak Terutang</small>
                        <h4 class="mb-0 fw-bold text-dark">Rp {{ number_format($metrics['total_pajak'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Nilai ketetapan SPPT</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-success"><i class="mdi mdi-cash-check mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Total Sudah Dibayar</small>
                        <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($metrics['total_bayar'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Realisasi pembayaran</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-danger"><i class="mdi mdi-alert-circle-outline mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Sisa Tunggakan</small>
                        <h4 class="mb-0 fw-bold text-danger">Rp {{ number_format($metrics['total_sisa'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Belum / kurang bayar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Form -->
<div class="card border shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('property-taxes.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Tahun Pajak</label>
                <select name="tahun" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                    <option value="{{ $yr }}" @selected((string)$filters['tahun'] === (string)$yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Status Bayar</label>
                <select name="status_bayar" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="LUNAS" @selected($filters['status_bayar'] === 'LUNAS')>LUNAS</option>
                    <option value="BELUM_LUNAS" @selected($filters['status_bayar'] === 'BELUM_LUNAS')>BELUM LUNAS</option>
                    <option value="KURANG_BAYAR" @selected($filters['status_bayar'] === 'KURANG_BAYAR')>KURANG BAYAR</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Kota / Kabupaten</label>
                <select name="kota_kab" class="form-select form-select-sm">
                    <option value="">Semua Wilayah</option>
                    @foreach($cities as $city)
                    <option value="{{ $city }}" @selected($filters['kota_kab'] === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Cabang Terkait</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected((string)$filters['branch_id'] === (string)$b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Pencarian NOP / Wajib Pajak / Alamat</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari NOP, Nama WP, Alamat..." value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Terapkan Filter"><i class="mdi mdi-magnify"></i> Filter</button>
                @if(request()->anyFilled(['tahun', 'status_bayar', 'kota_kab', 'branch_id', 'search']))
                <a href="{{ route('property-taxes.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter"><i class="mdi mdi-refresh"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 no-datatable" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>NOP &bull; Objek Pajak</th>
                        <th>Wajib Pajak (WP)</th>
                        <th>Tahun</th>
                        <th>Luas (Bumi/Bgn)</th>
                        <th class="text-end">Pajak Terutang</th>
                        <th class="text-end">SPPT Dibayar</th>
                        <th class="text-center">Status</th>
                        <th>Tgl Bayar</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($propertyTaxes as $idx => $tax)
                    <tr>
                        <td class="text-center text-muted">{{ $propertyTaxes->firstItem() + $idx }}</td>
                        <td>
                            <a href="{{ route('property-taxes.show', $tax) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                                {{ $tax->nop }}
                            </a>
                            <div class="small text-dark">{{ $tax->alamat_op }}</div>
                            <small class="text-muted">{{ $tax->desa ? $tax->desa . ', ' : '' }}{{ $tax->kecamatan ? $tax->kecamatan . ', ' : '' }}{{ $tax->kota_kab }}</small>
                            @if($tax->branch)
                            <div><span class="badge bg-light text-dark border"><i class="mdi mdi-source-branch"></i> {{ $tax->branch->name }}</span></div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $tax->nama_wp }}</div>
                            @if($tax->npwp)
                            <small class="text-muted font-monospace"><i class="mdi mdi-card-account-details-outline"></i> {{ $tax->npwp }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary font-monospace">{{ $tax->tahun }}</span></td>
                        <td>
                            <div class="small">B: <strong>{{ $tax->luas_bumi ? number_format($tax->luas_bumi, 0, ',', '.') : '-' }}</strong> m²</div>
                            <div class="small text-muted">G: <strong>{{ $tax->luas_bangunan ? number_format($tax->luas_bangunan, 0, ',', '.') : '-' }}</strong> m²</div>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            Rp {{ number_format($tax->pajak ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold {{ $tax->sppt_yg_dibayar >= $tax->pajak ? 'text-success' : 'text-primary' }}">
                            Rp {{ number_format($tax->sppt_yg_dibayar ?? 0, 0, ',', '.') }}
                            @if($tax->sisa_pajak > 0)
                            <div class="small text-danger fw-normal">Sisa: Rp {{ number_format($tax->sisa_pajak, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($tax->status_bayar === 'LUNAS')
                            <span class="badge bg-success"><i class="mdi mdi-check-circle"></i> LUNAS</span>
                            @elseif($tax->status_bayar === 'KURANG_BAYAR')
                            <span class="badge bg-warning text-dark"><i class="mdi mdi-alert"></i> KURANG BAYAR</span>
                            @else
                            <span class="badge bg-danger"><i class="mdi mdi-close-circle"></i> BELUM LUNAS</span>
                            @endif
                        </td>
                        <td>
                            @if($tax->tgl_bayar)
                            <span class="small text-dark">{{ $tax->tgl_bayar->format('d/m/Y') }}</span>
                            @if($tax->bukti_bayar)
                            <a href="{{ asset('storage/' . $tax->bukti_bayar) }}" target="_blank" class="text-primary ms-1" title="Lihat Bukti Bayar"><i class="mdi mdi-attachment"></i></a>
                            @endif
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('property-taxes.show', $tax) }}" class="btn btn-outline-info" title="Detail"><i class="mdi mdi-eye"></i></a>
                                <a href="{{ route('property-taxes.print', $tax) }}" target="_blank" class="btn btn-outline-secondary" title="Cetak"><i class="mdi mdi-printer"></i></a>
                                <a href="{{ route('property-taxes.edit', $tax) }}" class="btn btn-outline-primary" title="Ubah"><i class="mdi mdi-pencil"></i></a>
                                <form action="{{ route('property-taxes.destroy', $tax) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data PBB NOP {{ $tax->nop }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Hapus"><i class="mdi mdi-delete"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="mdi mdi-file-question-outline mdi-36px d-block mb-1 text-muted"></i>
                            Tidak ada data PBB ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($propertyTaxes->hasPages())
    <div class="card-footer bg-white border-top py-2 px-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-1 mb-md-0">
                Menampilkan <strong>{{ $propertyTaxes->firstItem() }}</strong> s/d <strong>{{ $propertyTaxes->lastItem() }}</strong> dari <strong>{{ $propertyTaxes->total() }}</strong> total data
            </small>
            <div class="d-flex align-items-center">
                {{ $propertyTaxes->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="modalImportCsv" tabindex="-1" role="dialog" aria-labelledby="modalImportCsvLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('property-taxes.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="modalImportCsvLabel"><i class="mdi mdi-upload"></i> Import Data PBB dari CSV</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Gunakan fitur ini untuk memasukkan data PBB dalam jumlah besar dari aplikasi sebelumnya.
                        Header kolom CSV yang didukung:
                        <code>nop, alamat_op, desa, kecamatan, kota_kab, nama_wp, npwp, tahun, luas_bumi, luas_bangunan, pajak, sppt_yg_dibayar, tgl_bayar, keterangan</code>
                    </p>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label font-weight-bold small">Pilih File CSV (.csv / .txt) <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted d-block mt-1">Ukuran maksimal file: 10 MB.</small>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="mdi mdi-information-outline"></i> Belum punya formatnya? <a href="{{ route('property-taxes.template') }}" class="font-weight-bold alert-link">Unduh Template CSV di sini</a>.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm text-white font-weight-bold"><i class="mdi mdi-check"></i> Proses Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnImportCsv, [data-target="#modalImportCsv"], [data-bs-target="#modalImportCsv"]').on('click', function(e) {
        e.preventDefault();
        $('#modalImportCsv').modal('show');
    });
});
</script>
@endpush

