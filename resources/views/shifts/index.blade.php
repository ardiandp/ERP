@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Shifts</h4>
    <a href="{{ route('shifts.create') }}" class="btn btn-primary btn-sm">Tambah Shift</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Branch</th><th>Start</th><th>End</th><th>Break</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($shifts as $shift)
        <tr>
            <td>{{ $shift->code }}</td>
            <td>{{ $shift->name }}</td>
            <td>{{ $shift->branch?->name }}</td>
            <td>{{ $shift->start_time }}</td>
            <td>{{ $shift->end_time }}</td>
            <td>{{ $shift->break_minutes }}</td>
            <td>{{ $shift->status }}</td>
            <td>
                <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('shifts.destroy', $shift) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8">Belum ada shift.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $shifts->links() }}

<div class="card mt-4">
    <div class="card-header">Penugasan Shift Karyawan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('shifts.assign') }}" class="row g-2 mb-3">
            @csrf
            <div class="col-md-4">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">-- karyawan --</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="shift_id" class="form-select form-select-sm">
                    <option value="">-- shift --</option>
                    @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="effective_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}"></div>
            <div class="col-md-2"><input type="date" name="end_date" class="form-control form-control-sm"></div>
            <div class="col-md-1"><button class="btn btn-sm btn-primary">Tambah</button></div>
        </form>
        <table class="table table-sm mb-0">
            <thead><tr><th>Karyawan</th><th>Shift</th><th>Effective</th><th>End</th><th></th></tr></thead>
            <tbody>
                @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->employee?->full_name }}</td>
                    <td>{{ $assignment->shift?->name }}</td>
                    <td>{{ $assignment->effective_date?->format('d/m/Y') }}</td>
                    <td>{{ $assignment->end_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('shifts.unassign', $assignment) }}" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5">Belum ada penugasan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
