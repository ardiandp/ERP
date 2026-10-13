@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Stock Transfers</h4>
    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>No</th><th>Date</th><th>From</th><th>To</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($transfers as $transfer)
        <tr>
            <td>{{ $transfer->transfer_number }}</td>
            <td>{{ optional($transfer->transfer_date)->format('Y-m-d') }}</td>
            <td>{{ $transfer->fromWarehouse?->name }}</td>
            <td>{{ $transfer->toWarehouse?->name }}</td>
            <td>{{ $transfer->status }}</td>
            <td><a href="{{ route('stock-transfers.show', $transfer) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada transfer.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $transfers->links() }}
@endsection
