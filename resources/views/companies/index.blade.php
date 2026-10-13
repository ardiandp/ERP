@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Companies</h4>
    <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($companies as $company)
        <tr>
            <td>{{ $company->code }}</td>
            <td><a href="{{ route('companies.show', $company) }}">{{ $company->name }}</a></td>
            <td>{{ $company->status }}</td>
            <td>
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('companies.destroy', $company) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $companies->links() }}
@endsection
