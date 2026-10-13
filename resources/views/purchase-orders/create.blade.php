@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Purchase Order</h4>
<form method="POST" action="{{ route('purchase-orders.store') }}">
    @csrf
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select">
                <option value="">-</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Tanggal</label><input type="date" name="order_date" class="form-control" value="{{ old('order_date', now()->toDateString()) }}"></div>
        <div class="col-md-2"><label class="form-label">Estimasi Datang</label><input type="date" name="expected_date" class="form-control" value="{{ old('expected_date') }}"></div>
        <div class="col-md-2"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"></div>
    </div>

    <table class="table table-bordered align-middle">
        <thead><tr><th style="width:40%">Product</th><th>Qty</th><th>Harga</th><th>Diskon</th><th>Subtotal</th><th></th></tr></thead>
        <tbody id="items-body"></tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-row">+ Tambah Item</button>

    <div class="row justify-content-end">
        <div class="col-md-5">
            <table class="table">
                <tr><th>Subtotal</th><td class="text-end" id="subtotal">0.00</td></tr>
                <tr><th>Diskon</th><td><input type="number" step="any" name="discount" id="discount" class="form-control form-control-sm text-end" value="{{ old('discount', 0) }}"></td></tr>
                <tr><th>Pajak</th><td><input type="number" step="any" name="tax" id="tax" class="form-control form-control-sm text-end" value="{{ old('tax', 0) }}"></td></tr>
                <tr><th>Grand Total</th><td class="text-end fw-bold" id="grand-total">0.00</td></tr>
            </table>
        </div>
    </div>

    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>

<template id="row-template">
    <tr>
        <td>
            <select name="items[__i__][product_id]" class="form-select form-select-sm">
                <option value="">- pilih -</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="any" name="items[__i__][quantity]" class="form-control form-control-sm item-qty" value="1"></td>
        <td><input type="number" step="any" name="items[__i__][unit_price]" class="form-control form-control-sm item-price" value="0"></td>
        <td><input type="number" step="any" name="items[__i__][discount]" class="form-control form-control-sm item-discount" value="0"></td>
        <td class="text-end item-subtotal">0.00</td>
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

    function recalc() {
        let subtotal = 0;
        body.querySelectorAll('tr').forEach(function (row) {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
            const line = Math.max(0, qty * price - discount);
            row.querySelector('.item-subtotal').textContent = line.toFixed(2);
            subtotal += line;
        });
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        const tdisc = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        document.getElementById('grand-total').textContent = Math.max(0, subtotal - tdisc + tax).toFixed(2);
    }

    document.getElementById('add-row').addEventListener('click', function () {
        body.insertAdjacentHTML('beforeend', template.replace(/__i__/g, index++));
        recalc();
    });
    body.addEventListener('input', recalc);
    body.addEventListener('change', function (event) {
        if (event.target.name && event.target.name.indexOf('product_id') !== -1) {
            const option = event.target.selectedOptions[0];
            const row = event.target.closest('tr');
            row.querySelector('.item-price').value = option ? (option.dataset.price || 0) : 0;
            recalc();
        }
    });
    document.getElementById('discount').addEventListener('input', recalc);
    document.getElementById('tax').addEventListener('input', recalc);
    body.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
            recalc();
        }
    });

    document.getElementById('add-row').click();
})();
</script>
@endpush
