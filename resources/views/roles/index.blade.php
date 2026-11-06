@extends('layouts.app')
@section('title', 'Peran & Hak Akses (Roles & Permissions)')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Peran &amp; Hak Akses (Roles &amp; Permissions)</h4>
        <span class="text-muted small">Daftar peran sistem dan matriks hak akses per fitur modul</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle datatable-table">
                <thead>
                    <tr>
                        <th style="width: 180px;">Nama Peran</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width: 100px;">Jumlah User</th>
                        <th class="text-center" style="width: 120px;">Jumlah Izin</th>
                        <th>Daftar Hak Akses (Permissions)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td class="fw-bold text-primary fs-6">{{ $role->name }}</td>
                        <td>{{ $role->description ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary fs-6">{{ $role->users_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info fs-6">{{ $role->permissions_count }}</span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1" style="max-height: 120px; overflow-y: auto;">
                                @forelse($role->permissions as $perm)
                                <span class="badge bg-light text-dark border">{{ $perm->name }}</span>
                                @empty
                                <span class="text-muted small">Semua hak akses (Super Admin) atau belum diatur.</span>
                                @endforelse
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
