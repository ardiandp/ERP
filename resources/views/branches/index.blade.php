@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Branches</h4>
    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Company</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($branches as $branch)
        <tr>
            <td>{{ $branch->code }}</td>
            <td>{{ $branch->name }}</td>
            <td>{{ $branch->company->name ?? '-' }}</td>
            <td>{{ $branch->status }}</td>
            <td>
                <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('branches.destroy', $branch) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
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
{{ $branches->links() }}
@endsection
