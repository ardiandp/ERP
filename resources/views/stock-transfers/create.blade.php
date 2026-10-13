@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Transfer Stok</h4>
<form method="POST" action="{{ route('stock-transfers.store') }}">
    @csrf
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Dari Gudang</label>
            <select name="from_warehouse_id" class="form-select">
                <option value="">-</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((string) old('from_warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Ke Gudang</label>
            <select name="to_warehouse_id" class="form-select">
                <option value="">-</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((string) old('to_warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Tanggal</label><input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', now()->toDateString()) }}"></div>
        <div class="col-md-4"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"></div>
    </div>

    <table class="table table-bordered align-middle">
        <thead><tr><th style="width:60%">Product</th><th>Qty</th><th></th></tr></thead>
        <tbody id="items-body"></tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-row">+ Tambah Item</button>

    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
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
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const body = document.getElementById('items-body');
    const template = document.getElementById('row-template').innerHTML;
    let index = 0;

    document.getElementById('add-row').addEventListener('click', function () {
        body.insertAdjacentHTML('beforeend', template.replace(/__i__/g, index++));
    });
    body.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
        }
    });
    document.getElementById('add-row').click();
})();
</script>
@endpush
