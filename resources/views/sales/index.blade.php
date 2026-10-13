@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Sales Transactions</h4>
    <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">POS / Kasir</a>
</div>
<form class="row g-2 mb-3">
    <div class="col-md-4"><input name="search" class="form-control" placeholder="Cari invoice" value="{{ request('search') }}"></div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Cari</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th class="text-end">Grand Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($transactions as $transaction)
        <tr>
            <td>{{ $transaction->invoice_number }}</td>
            <td>{{ optional($transaction->transaction_date)->format('Y-m-d') }}</td>
            <td>{{ $transaction->customer?->name ?? 'Umum' }}</td>
            <td class="text-end">{{ number_format((float) $transaction->grand_total, 2) }}</td>
            <td>{{ $transaction->status }}</td>
            <td><a href="{{ route('sales.show', $transaction) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada transaksi.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $transactions->links() }}
@endsection
