@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Warehouses</h4>
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Branch</th><th>Type</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($warehouses as $warehouse)
        <tr>
            <td>{{ $warehouse->code }}</td>
            <td>{{ $warehouse->name }}</td>
            <td>{{ $warehouse->branch?->name }}</td>
            <td>{{ $warehouse->warehouse_type }}</td>
            <td>{{ $warehouse->status }}</td>
            <td>
                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $warehouses->links() }}
@endsection
