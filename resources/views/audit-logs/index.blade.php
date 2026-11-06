@extends('layouts.app')
@section('title', 'Audit Log Aktivitas Sistem')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Audit Log Aktivitas Sistem</h4>
        <span class="text-muted small">Rekam jejak seluruh perubahan data, login, dan aksi pengguna pada sistem</span>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Aksi</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                    <option value="{{ $act }}" @selected(request('action') === $act)>{{ strtoupper($act) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Entitas / Modul</label>
                <select name="auditable_type" class="form-select form-select-sm">
                    <option value="">Semua Entitas</option>
                    @foreach($entityTypes as $et)
                    <option value="{{ $et }}" @selected(request('auditable_type') === $et)>{{ $et }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Pengguna</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">Filter</button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle datatable-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Entitas / Modul</th>
                        <th>ID Objek</th>
                        <th>IP Address</th>
                        <th class="text-center">Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                        <td class="fw-bold">{{ $log->user?->name ?? 'System' }}</td>
                        <td>
                            @php
                                $badgeClass = match(strtolower($log->action)) {
                                    'login' => 'bg-info',
                                    'logout' => 'bg-secondary',
                                    'create' => 'bg-success',
                                    'update' => 'bg-primary',
                                    'delete' => 'bg-danger',
                                    'approve' => 'bg-teal text-white',
                                    'reject' => 'bg-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($log->action) }}</span>
                        </td>
                        <td>{{ $log->auditable_type ? class_basename($log->auditable_type) : '-' }}</td>
                        <td>{{ $log->auditable_id ?? '-' }}</td>
                        <td><small class="text-muted">{{ $log->ip_address ?? '-' }}</small></td>
                        <td class="text-center">
                            @if($log->old_values || $log->new_values)
                            <button type="button" class="btn btn-sm btn-outline-info view-diff-btn" 
                                data-id="{{ $log->id }}"
                                data-action="{{ strtoupper($log->action) }}"
                                data-entity="{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}"
                                data-old='@json($log->old_values)'
                                data-new='@json($log->new_values)'>
                                Lihat Data
                            </button>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada riwayat audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETAIL PERUBAHAN --}}
<div class="modal fade" id="auditModal" tabindex="-1" aria-labelledby="auditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditModalLabel">Detail Perubahan Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Aksi:</strong> <span id="modalAction" class="badge bg-primary"></span>
                    <strong class="ms-3">Objek:</strong> <span id="modalEntity"></span>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-danger fw-bold border-bottom pb-1">Nilai Sebelum (Old Values)</h6>
                        <pre id="modalOld" class="bg-light p-2 rounded" style="max-height: 300px; font-size: 12px; overflow-y:auto;">-</pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success fw-bold border-bottom pb-1">Nilai Sesudah (New Values)</h6>
                        <pre id="modalNew" class="bg-light p-2 rounded" style="max-height: 300px; font-size: 12px; overflow-y:auto;">-</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('click', '.view-diff-btn', function () {
        var action = $(this).data('action');
        var entity = $(this).data('entity');
        var oldVal = $(this).data('old');
        var newVal = $(this).data('new');

        $('#modalAction').text(action);
        $('#modalEntity').text(entity);
        $('#modalOld').text(oldVal ? JSON.stringify(oldVal, null, 2) : '(Kosong / Baru dibuat)');
        $('#modalNew').text(newVal ? JSON.stringify(newVal, null, 2) : '(Kosong / Dihapus)');

        var modal = new bootstrap.Modal(document.getElementById('auditModal'));
        modal.show();
    });
});
</script>
@endpush
@endsection
