@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Inventory Stock</h4>
    <a href="{{ route('inventory.adjustment.create') }}" class="btn btn-primary btn-sm">Adjustment</a>
</div>
<form class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="warehouse_id" class="form-select">
            <option value="">Semua Gudang</option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" @selected((string) request('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><input name="search" class="form-control" placeholder="Cari produk" value="{{ request('search') }}"></div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Warehouse</th><th>Product</th><th class="text-end">Stock</th><th class="text-end">Reserved</th><th class="text-center" style="width: 15%;">Aksi</th></tr></thead>
    <tbody>
        @forelse($stocks as $stock)
        <tr>
            <td>{{ $stock->warehouse?->name }}</td>
            <td>{{ $stock->product?->name }} <small class="text-muted">({{ $stock->product?->sku }})</small></td>
            <td class="text-end fw-bold">{{ number_format((float) $stock->quantity, 0, ',', '.') }}</td>
            <td class="text-end text-muted">{{ number_format((float) $stock->reserved_quantity, 0, ',', '.') }}</td>
            <td class="text-center">
                <a href="{{ route('inventory.movements', ['warehouse_id' => $stock->warehouse_id, 'product_id' => $stock->product_id]) }}" class="btn btn-sm btn-outline-info" title="Lihat Kartu Stok Mutasi">
                    <i class="mdi mdi-history"></i> Kartu Stok
                </a>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
