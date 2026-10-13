@extends('layouts.app')
@section('content')
<h4 class="mb-3">Inventory Movements</h4>
<form class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="warehouse_id" class="form-select">
            <option value="">Semua Gudang</option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" @selected((string) request('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="product_id" class="form-select">
            <option value="">Semua Produk</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}" @selected((string) request('product_id') === (string) $product->id)>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Date</th><th>Warehouse</th><th>Product</th><th>Type</th><th class="text-end">Qty</th><th>Notes</th></tr></thead>
    <tbody>
        @forelse($movements as $movement)
        <tr>
            <td>{{ optional($movement->movement_date)->format('Y-m-d') }}</td>
            <td>{{ $movement->warehouse?->name }}</td>
            <td>{{ $movement->product?->name }}</td>
            <td>{{ $movement->movement_type }}</td>
            <td class="text-end">{{ number_format((float) $movement->quantity, 2) }}</td>
            <td>{{ $movement->notes }}</td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $movements->links() }}
@endsection
