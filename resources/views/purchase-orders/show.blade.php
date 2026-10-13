@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Purchase Order {{ $order->po_number }}</h4>
    <div>
        <a href="{{ route('goods-receipts.create', ['purchase_order' => $order->id]) }}" class="btn btn-outline-primary btn-sm">Terima Barang</a>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>Supplier</th><td>{{ $order->supplier?->name }}</td></tr>
            <tr><th>Branch</th><td>{{ $order->branch?->name }}</td></tr>
            <tr><th>Tanggal</th><td>{{ optional($order->order_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Estimasi Datang</th><td>{{ optional($order->expected_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Status</th><td>{{ $order->status }}</td></tr>
            <tr><th>Catatan</th><td>{{ $order->notes }}</td></tr>
        </table>
        @if($order->status === 'DRAFT')
        <form method="POST" action="{{ route('purchase-orders.approve', $order) }}" class="d-inline">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm">Setujui PO</button>
        </form>
        @endif
    </div>
</div>

@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<table class="table table-striped">
    <thead><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Diskon</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product?->name }}</td>
            <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->discount, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><th colspan="4" class="text-end">Subtotal</th><td class="text-end">{{ number_format((float) $order->subtotal, 2) }}</td></tr>
        <tr><th colspan="4" class="text-end">Diskon</th><td class="text-end">{{ number_format((float) $order->discount, 2) }}</td></tr>
        <tr><th colspan="4" class="text-end">Pajak</th><td class="text-end">{{ number_format((float) $order->tax, 2) }}</td></tr>
        <tr><th colspan="4" class="text-end">Grand Total</th><td class="text-end fw-bold">{{ number_format((float) $order->grand_total, 2) }}</td></tr>
    </tfoot>
</table>

@if($order->receipts->isNotEmpty())
<h5>Penerimaan</h5>
<table class="table table-sm">
    <thead><tr><th>No</th><th>Tanggal</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($order->receipts as $receipt)
        <tr>
            <td><a href="{{ route('goods-receipts.show', $receipt) }}">{{ $receipt->receipt_number }}</a></td>
            <td>{{ optional($receipt->receipt_date)->format('Y-m-d') }}</td>
            <td>{{ $receipt->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
