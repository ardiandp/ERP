@extends('layouts.app')
@section('title', 'Edit Pengguna: ' . $user->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Edit Pengguna: {{ $user->name }}</h4>
        <span class="text-muted small">Perbarui data login, status, dan penugasan peran</span>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Login <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password Baru (Opsional)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Biarkan kosong jika tidak ingin mengubah password.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tautkan ke Data Karyawan</label>
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror">
                        <option value="">-- Bukan Karyawan / Akun Khusus --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id', $user->employee_id) == $emp->id)>
                            {{ $emp->full_name }} ({{ $emp->branch?->name ?? 'Pusat' }} - {{ $emp->position?->name ?? 'Staff' }})
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Akun <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="ACTIVE" @selected(old('status', $user->status) === 'ACTIVE')>ACTIVE (Aktif)</option>
                        <option value="INACTIVE" @selected(old('status', $user->status) === 'INACTIVE')>INACTIVE (Nonaktif)</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <label class="form-label fw-bold">Hak Akses / Peran (Roles)</label>
                    @php
                        $userRoleIds = old('roles', $user->roles->pluck('id')->all());
                    @endphp
                    <div class="row g-2">
                        @foreach($roles as $role)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                    @checked(in_array($role->id, (array) $userRoleIds))>
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    <strong>{{ $role->name }}</strong>
                                    @if($role->description)
                                    <small class="d-block text-muted">{{ $role->description }}</small>
                                    @endif
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
