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
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Gudang</th>
            <th>Produk</th>
            <th>Tipe Mutasi</th>
            <th class="text-end">Jumlah</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movements as $movement)
        @php
            $isNegative = in_array($movement->movement_type, ['SALE', 'TRANSFER_OUT', 'RETURN_TO_SUPPLIER']);
            $badgeColor = match($movement->movement_type) {
                'INITIAL', 'PURCHASE_RECEIPT', 'TRANSFER_IN' => 'bg-success',
                'SALE', 'TRANSFER_OUT' => 'bg-danger',
                'OPNAME_ADJUST', 'ADJUSTMENT' => 'bg-warning text-dark',
                default => 'bg-info',
            };
        @endphp
        <tr>
            <td>{{ optional($movement->movement_date)->format('d/m/Y') }}</td>
            <td>{{ $movement->warehouse?->name }}</td>
            <td>
                <strong>{{ $movement->product?->name }}</strong>
                <small class="text-muted d-block">SKU: {{ $movement->product?->sku }}</small>
            </td>
            <td><span class="badge {{ $badgeColor }}">{{ $movement->movement_type }}</span></td>
            <td class="text-end fw-bold {{ $isNegative ? 'text-danger' : 'text-success' }}">
                {{ $isNegative ? '-' : '+' }} {{ number_format((float) $movement->quantity, 0, ',', '.') }}
            </td>
            <td><small class="text-muted">{{ $movement->notes ?? '-' }}</small></td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada data mutasi persediaan.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
