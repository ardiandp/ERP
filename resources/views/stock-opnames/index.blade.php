@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Stock Opname</h4>
    <a href="{{ route('stock-opnames.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>No</th><th>Date</th><th>Warehouse</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($opnames as $opname)
        <tr>
            <td>{{ $opname->opname_number }}</td>
            <td>{{ optional($opname->opname_date)->format('Y-m-d') }}</td>
            <td>{{ $opname->warehouse?->name }}</td>
            <td>{{ $opname->status }}</td>
            <td><a href="{{ route('stock-opnames.show', $opname) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="5">Belum ada opname.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $opnames->links() }}
@endsection
