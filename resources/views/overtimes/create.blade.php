@extends('layouts.app')
@section('content')
<h4 class="mb-3">Catat Lembur</h4>
<form method="POST" action="{{ route('overtimes.store') }}">
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
        <div class="col-md-4 mb-3"><label class="form-label">Attendances (opsional)</label>
            <select name="attendance_id" class="form-select">
                <option value="">-</option>
                @foreach($attendances as $attendance)
                <option value="{{ $attendance->id }}" @selected((string) old('attendance_id') === (string) $attendance->id)>{{ $attendance->employee?->full_name }} - {{ $attendance->attendance_date?->format('d/m/Y') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Tanggal Lembur</label><input type="date" name="overtime_date" class="form-control" value="{{ old('overtime_date', now()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Mulai</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Selesai</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}"></div>
    </div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
