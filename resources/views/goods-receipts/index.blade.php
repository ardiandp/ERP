@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Goods Receipts</h4>
    <a href="{{ route('goods-receipts.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<table class="table table-striped">
    <thead><tr><th>No</th><th>Date</th><th>Supplier</th><th>PO</th><th>Warehouse</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($receipts as $receipt)
        <tr>
            <td>{{ $receipt->receipt_number }}</td>
            <td>{{ optional($receipt->receipt_date)->format('Y-m-d') }}</td>
            <td>{{ $receipt->supplier?->name }}</td>
            <td>{{ $receipt->purchaseOrder?->po_number }}</td>
            <td>{{ $receipt->warehouse?->name }}</td>
            <td>{{ $receipt->status }}</td>
            <td><a href="{{ route('goods-receipts.show', $receipt) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada penerimaan.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $receipts->links() }}
@endsection
