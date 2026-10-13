@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Attendances</h4>
    <a href="{{ route('attendances.create') }}" class="btn btn-primary btn-sm">Catat Absensi</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto"><input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}"></div>
    <div class="col-auto">
        <select name="employee_id" class="form-select form-select-sm">
            <option value="">Semua Karyawan</option>
            @foreach($employees as $employee)
            <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Tanggal</th><th>Karyawan</th><th>Branch</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($attendances as $attendance)
        <tr>
            <td>{{ $attendance->attendance_date?->format('d/m/Y') }}</td>
            <td>{{ $attendance->employee?->full_name }}</td>
            <td>{{ $attendance->branch?->name }}</td>
            <td>{{ $attendance->check_in }}</td>
            <td>{{ $attendance->check_out }}</td>
            <td>{{ $attendance->status }}</td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada absensi.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $attendances->links() }}
@endsection
