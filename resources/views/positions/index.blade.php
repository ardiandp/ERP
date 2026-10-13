@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Positions</h4>
    <a href="{{ route('positions.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($positions as $position)
        <tr>
            <td>{{ $position->code }}</td>
            <td>{{ $position->name }}</td>
            <td>{{ $position->department->name ?? '-' }}</td>
            <td>{{ $position->status }}</td>
            <td>
                <a href="{{ route('positions.edit', $position) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('positions.destroy', $position) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
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
{{ $positions->links() }}
@endsection
