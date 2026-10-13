@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Penerimaan {{ $receipt->receipt_number }}</h4>
    <a href="{{ route('goods-receipts.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>Supplier</th><td>{{ $receipt->supplier?->name }}</td></tr>
            <tr><th>Branch</th><td>{{ $receipt->branch?->name }}</td></tr>
            <tr><th>Warehouse</th><td>{{ $receipt->warehouse?->name }}</td></tr>
            <tr><th>PO</th><td>{{ $receipt->purchaseOrder?->po_number ?? '-' }}</td></tr>
            <tr><th>Tanggal</th><td>{{ optional($receipt->receipt_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Status</th><td>{{ $receipt->status }}</td></tr>
        </table>
    </div>
</div>

<table class="table table-striped">
    <thead><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Unit Cost</th></tr></thead>
    <tbody>
        @foreach($receipt->items as $item)
        <tr>
            <td>{{ $item->product?->name }}</td>
            <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->unit_cost, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
