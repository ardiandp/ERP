@extends('layouts.app')
@section('title', 'Manajemen Pengguna (Users)')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Manajemen Pengguna (Users)</h4>
        <span class="text-muted small">Kelola akun akses sistem, hak akses, dan hubungan dengan data karyawan</span>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="mdi mdi-account-plus me-1"></i> Tambah Pengguna
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle datatable-table">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Karyawan &amp; Cabang</th>
                        <th>Status</th>
                        <th>Peran (Roles)</th>
                        <th>Login Terakhir</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="fw-bold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->employee)
                            <div>{{ $user->employee->full_name }}</div>
                            <small class="text-muted">{{ $user->employee->branch?->name ?? 'Pusat' }}</small>
                            @else
                            <span class="text-muted small">Akun Non-Karyawan</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $user->status === 'ACTIVE' ? 'bg-success' : 'bg-danger' }}">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                            <span class="badge bg-info text-white">{{ $role->name }}</span>
                            @empty
                            <span class="text-muted small">-</span>
                            @endforelse
                        </td>
                        <td>{{ optional($user->last_login_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary">
                                    <i class="mdi mdi-pencil"></i> Edit
                                </a>
                                @if(auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline" onsubmit="return confirm('Ubah status aktif pengguna ini?')">
                                    @csrf @method('PATCH')
                                    <button class="btn {{ $user->status === 'ACTIVE' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $user->status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengguna terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
