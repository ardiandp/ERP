@extends('layouts.app')
@section('title', 'Mutasi Rekening Bank')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-0 text-dark fw-bold"><i class="mdi mdi-bank-transfer text-primary"></i> Mutasi Rekening Bank</h4>
        <small class="text-muted">Pencatatan dan rekonsiliasi transaksi rekening koran bank operasional</small>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="{{ route('bank-transactions.create') }}" class="btn btn-outline-secondary btn-sm me-1 shadow-sm">
            <i class="mdi mdi-plus"></i> Tambah Manual
        </a>
        <button type="button" class="btn btn-primary btn-sm me-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportBca">
            <i class="mdi mdi-upload"></i> Import Mutasi BCA
        </button>
        <a href="{{ route('bank-transactions.export', request()->query()) }}" class="btn btn-success btn-sm shadow-sm" title="Ekspor mutasi bank ke CSV">
            <i class="mdi mdi-file-excel"></i> Export CSV
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="mdi mdi-alert-circle me-1"></i> {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Quick Account Switcher (Tab Buttons) -->
<div class="card border shadow-sm mb-3">
    <div class="card-body p-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-1">
                <span class="text-muted small fw-bold me-2 ps-2"><i class="mdi mdi-wallet-membership"></i> Rekening:</span>
                <a href="{{ route('bank-transactions.index', array_merge(request()->except('financial_account_id', 'page'))) }}" 
                   class="btn btn-sm {{ empty($selectedAccountId) ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua Rekening
                </a>
                @foreach($accounts as $acc)
                <a href="{{ route('bank-transactions.index', array_merge(request()->except('page'), ['financial_account_id' => $acc->id])) }}" 
                   class="btn btn-sm {{ (string)$selectedAccountId === (string)$acc->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                    @if(stripos($acc->account_name, 'CV') !== false)
                    <i class="mdi mdi-domain"></i>
                    @else
                    <i class="mdi mdi-account"></i>
                    @endif
                    {{ $acc->account_name }}
                    @if($acc->account_number)
                    <span class="badge bg-light text-dark ms-1 font-monospace" style="font-size: 11px;">{{ $acc->account_number }}</span>
                    @endif
                </a>
                @endforeach
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRekening" title="Daftarkan Rekening Bank Baru">
                    <i class="mdi mdi-plus-circle"></i> Tambah Rekening
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Metrics Cards -->
<div class="row g-2 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-primary"><i class="mdi mdi-wallet mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">
                            {{ $selectedAccount ? 'Saldo Rekening ' . $selectedAccount->account_name : 'Arus Bersih (Net Flow)' }}
                        </small>
                        <h4 class="mb-0 fw-bold {{ $selectedAccount ? 'text-primary' : ($metrics['net_flow'] >= 0 ? 'text-success' : 'text-danger') }}">
                            Rp {{ number_format($selectedAccount ? $metrics['latest_balance'] : $metrics['net_flow'], 0, ',', '.') }}
                        </h4>
                        <small class="text-muted" style="font-size: 11px;">
                            {{ $selectedAccount ? 'Saldo terakhir tercatat' : 'Kredit masuk dikurangi debet' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-success"><i class="mdi mdi-arrow-down-bold-circle mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Total Uang Masuk (CR)</small>
                        <h4 class="mb-0 fw-bold text-success">+ Rp {{ number_format($metrics['total_in'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Transfer masuk / setoran</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-danger"><i class="mdi mdi-arrow-up-bold-circle mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Total Uang Keluar (DB)</small>
                        <h4 class="mb-0 fw-bold text-danger">- Rp {{ number_format($metrics['total_out'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 11px;">Penarikan / beban / transfer</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-white border shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-warning"><i class="mdi mdi-checkbox-blank-circle-outline mdi-36px"></i></div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Belum Direkonsiliasi</small>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($metrics['unreconciled_count']) }} Transaksi</h4>
                        <small class="text-muted" style="font-size: 11px;">Menunggu pencocokan pembayaran</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border shadow-sm mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('bank-transactions.index') }}" class="row g-2 align-items-end">
            @if($selectedAccountId)
            <input type="hidden" name="financial_account_id" value="{{ $selectedAccountId }}">
            @endif
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Status Rekonsiliasi</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="UNRECONCILED" @selected($filters['status'] === 'UNRECONCILED')>UNRECONCILED (Belum)</option>
                    <option value="RECONCILED" @selected($filters['status'] === 'RECONCILED')>RECONCILED (Selesai)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Jenis Transaksi</label>
                <select name="transaction_type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="IN" @selected($filters['transaction_type'] === 'IN')>IN &bull; Uang Masuk (CR)</option>
                    <option value="OUT" @selected($filters['transaction_type'] === 'OUT')>OUT &bull; Uang Keluar (DB)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Pencarian Keterangan / Referensi</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama, berita transfer, no. ref..." value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Terapkan Filter"><i class="mdi mdi-magnify"></i></button>
                @if(request()->anyFilled(['status', 'transaction_type', 'from', 'to', 'search']))
                <a href="{{ route('bank-transactions.index', $selectedAccountId ? ['financial_account_id' => $selectedAccountId] : []) }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter"><i class="mdi mdi-refresh"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card border shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th style="width: 100px;">Tanggal</th>
                        @if(!$selectedAccountId)
                        <th>Akun Rekening Bank</th>
                        @endif
                        <th style="width: 80px;" class="text-center">Jenis</th>
                        <th class="text-end" style="width: 140px;">Jumlah (Rp)</th>
                        <th class="text-end" style="width: 140px;">Saldo (Rp)</th>
                        <th>Keterangan / Berita Transfer</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $idx => $t)
                    <tr>
                        <td class="text-center text-muted">{{ $transactions->firstItem() + $idx }}</td>
                        <td class="font-monospace fw-semibold">{{ $t->transaction_date?->format('d/m/Y') }}</td>
                        @if(!$selectedAccountId)
                        <td>
                            @php
                                $accName = $t->financialAccount?->account_name ?? 'BCA';
                                $badgeColor = 'bg-primary text-white';
                                if (stripos($accName, 'Alya') !== false || stripos($accName, 'Aliya') !== false) {
                                    $badgeColor = 'bg-success text-white';
                                } elseif (stripos($accName, 'Akhdan') !== false) {
                                    $badgeColor = 'bg-info text-dark';
                                } elseif (stripos($accName, 'CV') !== false) {
                                    $badgeColor = 'bg-dark text-white';
                                }
                            @endphp
                            <span class="badge {{ $badgeColor }}" style="font-size: 11px;">
                                {{ $accName }}
                            </span>
                            @if($t->financialAccount?->account_number)
                            <div class="small text-muted font-monospace" style="font-size: 11px;">{{ $t->financialAccount->account_number }}</div>
                            @endif
                        </td>
                        @endif
                        <td class="text-center">
                            @if($t->transaction_type === 'IN')
                            <span class="badge bg-success-subtle text-success border border-success fw-bold px-2">CR &bull; IN</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger border border-danger fw-bold px-2">DB &bull; OUT</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $t->transaction_type === 'IN' ? 'text-success' : 'text-danger' }}">
                            {{ $t->transaction_type === 'IN' ? '+' : '-' }} {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                        <td class="text-end font-monospace text-muted">
                            {{ $t->balance !== null ? number_format($t->balance, 2, ',', '.') : '-' }}
                        </td>
                        <td>
                            <div class="text-dark">{{ $t->description }}</div>
                            @if($t->reference_number)
                            <small class="text-muted font-monospace"><i class="mdi mdi-tag-outline"></i> Ref: {{ $t->reference_number }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($t->reconciliation_status === 'RECONCILED')
                            <span class="badge bg-success"><i class="mdi mdi-check"></i> RECONCILED</span>
                            @else
                            <span class="badge bg-secondary">UNRECONCILED</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($t->reconciliation_status !== 'RECONCILED')
                            <a href="{{ route('bank-reconciliations.create', ['bank_transaction' => $t->id]) }}" 
                               class="btn btn-sm btn-outline-primary py-0 px-2 shadow-sm" style="font-size: 11px;" title="Cocokkan dengan invoice penjualan / pengeluaran">
                                <i class="mdi mdi-link-variant"></i> Reconcile
                            </a>
                            @else
                            <span class="text-success small"><i class="mdi mdi-check-all"></i> Terhubung</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $selectedAccountId ? 8 : 9 }}" class="text-center py-4 text-muted">
                            <i class="mdi mdi-file-question-outline mdi-36px d-block mb-1 text-muted"></i>
                            Belum ada data mutasi bank ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-white border-top py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Menampilkan {{ $transactions->firstItem() }} s/d {{ $transactions->lastItem() }} dari {{ $transactions->total() }} mutasi</small>
            {{ $transactions->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal Import BCA (Auto-Detect Individu & Bisnis) -->
<div class="modal fade" id="modalImportBca" tabindex="-1" aria-labelledby="modalImportBcaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bank-transactions.import-bca') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalImportBcaLabel"><i class="mdi mdi-bank-transfer-in"></i> Import Mutasi Bank BCA</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="mdi mdi-information-outline"></i> <strong>Deteksi Otomatis Cerdas:</strong>
                        Sistem mengenali langsung format <strong>BCA Individu (Akhdan, Alya)</strong> maupun <strong>BCA Bisnis (CV Dian Mustika)</strong> serta otomatis mencocokkan nomor rekening dari isi berkas.
                    </div>

                    <div class="mb-3">
                        <label for="csv_file" class="form-label fw-bold small">Pilih Berkas CSV Mutasi BCA <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv,.txt" required>
                        <small class="text-muted">Format: Ekspor file CSV dari KlikBCA Individu atau KlikBCA Bisnis (Maks. 15 MB).</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="year" class="form-label fw-bold small">Tahun Transaksi</label>
                            <input type="number" name="year" id="year" class="form-control" min="2000" max="2100" value="{{ date('Y') }}">
                            <small class="text-muted">Untuk format tanpa tahun di kolom tanggal.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="financial_account_id" class="form-label fw-bold small">Akun Bank Tujuan</label>
                            <select name="financial_account_id" id="financial_account_id" class="form-select">
                                <option value="">-- Otomatis Dari No. Rekening --</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected($selectedAccountId === $acc->id)>{{ $acc->account_name }} ({{ $acc->account_number ?: '-' }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Biarkan kosong agar sistem mencocokkan otomatis.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-check"></i> Proses Import Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Rekening Bank Baru -->
<div class="modal fade" id="modalTambahRekening" tabindex="-1" aria-labelledby="modalTambahRekeningLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bank-transactions.accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalTambahRekeningLabel"><i class="mdi mdi-bank-plus"></i> Tambah Rekening Bank Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border py-2 px-3 small mb-3">
                        <i class="mdi mdi-information text-primary"></i> 
                        Daftarkan rekening baru (misal BCA individu atas nama Laya, Alya, rekening operasional, dsb). 
                        Rekening ini otomatis menjadi tab baru dan target impor mutasi.
                    </div>

                    <div class="mb-3">
                        <label for="new_bank_id" class="form-label fw-bold small">Pilih Bank</label>
                        <select name="bank_id" id="new_bank_id" class="form-select">
                            @foreach($banks as $b)
                            <option value="{{ $b->id }}" @selected(strtoupper($b->code) === 'BCA')>{{ $b->name }} ({{ $b->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="new_account_number" class="form-label fw-bold small">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" id="new_account_number" class="form-control font-monospace" placeholder="Contoh: 8840123456" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_account_name" class="form-label fw-bold small">Nama Akun / Atas Nama <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" id="new_account_name" class="form-control" placeholder="Contoh: BCA Laya atau BCA CV Dian Mustika" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_opening_balance" class="form-label fw-bold small">Saldo Awal (Rp)</label>
                        <input type="number" step="0.01" name="opening_balance" id="new_opening_balance" class="form-control" value="0" min="0">
                        <small class="text-muted">Saldo awal pembukuan sebelum mutasi pertama.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-check"></i> Simpan Rekening</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
