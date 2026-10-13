@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Purchase Orders</h4>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<form class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            @foreach(['DRAFT', 'APPROVED', 'PARTIALLY_RECEIVED', 'RECEIVED', 'CANCELLED'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>PO</th><th>Date</th><th>Supplier</th><th>Branch</th><th class="text-end">Grand Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($orders as $order)
        <tr>
            <td>{{ $order->po_number }}</td>
            <td>{{ optional($order->order_date)->format('Y-m-d') }}</td>
            <td>{{ $order->supplier?->name }}</td>
            <td>{{ $order->branch?->name }}</td>
            <td class="text-end">{{ number_format((float) $order->grand_total, 2) }}</td>
            <td>{{ $order->status }}</td>
            <td><a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada purchase order.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $orders->links() }}
@endsection
