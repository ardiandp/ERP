@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Assets</h4>
    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">Tambah Aset</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Branch</th><th>Condition</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($assets as $asset)
        <tr>
            <td>{{ $asset->asset_code }}</td>
            <td>{{ $asset->name }}</td>
            <td>{{ $asset->category?->name }}</td>
            <td>{{ $asset->branch?->name }}</td>
            <td>{{ $asset->condition }}</td>
            <td>{{ $asset->status }}</td>
            <td>
                <a href="{{ route('assets.show', $asset) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada aset.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $assets->links() }}
@endsection
