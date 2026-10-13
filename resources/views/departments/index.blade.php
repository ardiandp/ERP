@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Departments</h4>
    <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Company</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($departments as $department)
        <tr>
            <td>{{ $department->code }}</td>
            <td>{{ $department->name }}</td>
            <td>{{ $department->company->name ?? '-' }}</td>
            <td>{{ $department->status }}</td>
            <td>
                <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('departments.destroy', $department) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $departments->links() }}
@endsection
