@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Penerimaan Barang</h4>
<form method="POST" action="{{ route('goods-receipts.store') }}">
    @csrf
    @if($purchaseOrder)
    <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">
    @endif
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select">
                <option value="">-</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $purchaseOrder->supplier_id ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $purchaseOrder->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Warehouse</label>
            <select name="warehouse_id" class="form-select">
                <option value="">Default</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date', now()->toDateString()) }}"></div>
        <div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"></div>
    </div>

    <table class="table table-bordered align-middle">
        <thead><tr><th style="width:50%">Product</th><th>Qty</th><th>Unit Cost</th><th></th></tr></thead>
        <tbody id="items-body">
            @if($purchaseOrder)
                @foreach($purchaseOrder->items as $i => $item)
                <tr>
                    <td>
                        <select name="items[{{ $i }}][product_id]" class="form-select form-select-sm">
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected($product->id === $item->product_id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="any" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ $item->quantity }}"></td>
                    <td><input type="number" step="any" name="items[{{ $i }}][unit_cost]" class="form-control form-control-sm" value="{{ $item->unit_price }}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-row">+ Tambah Item</button>

    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan Penerimaan</button>
</form>

<template id="row-template">
    <tr>
        <td>
            <select name="items[__i__][product_id]" class="form-select form-select-sm">
                <option value="">- pilih -</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="any" name="items[__i__][quantity]" class="form-control form-control-sm" value="1"></td>
        <td><input type="number" step="any" name="items[__i__][unit_cost]" class="form-control form-control-sm" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const body = document.getElementById('items-body');
    const template = document.getElementById('row-template').innerHTML;
    let index = {{ $purchaseOrder ? $purchaseOrder->items->count() : 0 }};

    document.getElementById('add-row').addEventListener('click', function () {
        body.insertAdjacentHTML('beforeend', template.replace(/__i__/g, index++));
    });
    body.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
        }
    });

    if (body.querySelectorAll('tr').length === 0) {
        document.getElementById('add-row').click();
    }
})();
</script>
@endpush
