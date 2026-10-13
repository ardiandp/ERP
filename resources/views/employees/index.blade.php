@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Employees</h4>
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Branch</th><th>Department</th><th>Position</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($employees as $employee)
        <tr>
            <td>{{ $employee->employee_code }}</td>
            <td><a href="{{ route('employees.show', $employee) }}">{{ $employee->full_name }}</a></td>
            <td>{{ $employee->branch->name ?? '-' }}</td>
            <td>{{ $employee->department->name ?? '-' }}</td>
            <td>{{ $employee->position->name ?? '-' }}</td>
            <td>{{ $employee->status }}</td>
            <td>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $employees->links() }}
@endsection
