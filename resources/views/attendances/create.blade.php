@extends('layouts.app')
@section('content')
<h4 class="mb-3">Catat Absensi</h4>
<form method="POST" action="{{ route('attendances.store') }}">
    @csrf
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Karyawan</label>
            <select name="employee_id" class="form-select">
                <option value="">-- pilih --</option>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
                <option value="">-- pilih --</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Tanggal</label><input type="date" name="attendance_date" class="form-control" value="{{ old('attendance_date', now()->toDateString()) }}"></div>
        <div class="col-md-3 mb-3"><label class="form-label">Check In</label><input type="time" name="check_in" class="form-control" value="{{ old('check_in') }}"></div>
        <div class="col-md-3 mb-3"><label class="form-label">Check Out</label><input type="time" name="check_out" class="form-control" value="{{ old('check_out') }}"></div>
        <div class="col-md-3 mb-3"><label class="form-label">Status</label>
            <select name="status" class="form-select">
                @foreach(['PRESENT', 'ABSENT', 'LATE', 'LEAVE', 'SICK', 'HOLIDAY'] as $status)
                <option value="{{ $status }}" @selected(old('status', 'PRESENT') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-3"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"></div>
    </div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
