@extends('layouts.app')
@section('content')
<h4 class="mb-3">POS / Kasir</h4>
<form method="POST" action="{{ route('sales.store') }}" id="pos-form">
    @csrf
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-select">
                <option value="">Umum</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Warehouse (stok)</label>
            <select name="warehouse_id" class="form-select">
                <option value="">Default</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', now()->toDateString()) }}">
        </div>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr><th style="width:34%">Item</th><th>Qty</th><th>Harga</th><th>Diskon</th><th>Terapis</th><th>Subtotal</th><th></th></tr>
        </thead>
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
                <tr>
                    <th>Bayar Via</th>
                    <td>
                        <select name="payment_method_id" class="form-select form-select-sm">
                            <option value="">Belum bayar</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" @selected((string) old('payment_method_id') === (string) $method->id)>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr><th>Jumlah Bayar</th><td><input type="number" step="any" name="paid_amount" class="form-control form-control-sm text-end" value="{{ old('paid_amount', 0) }}"></td></tr>
                <tr><th>Catatan</th><td><textarea name="notes" class="form-control form-control-sm">{{ old('notes') }}</textarea></td></tr>
            </table>
        </div>
    </div>

    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan Transaksi</button>
</form>

<template id="row-template">
    <tr>
        <td>
            <select name="items[__i__][ref]" class="form-select form-select-sm item-ref">
                <option value="">- pilih -</option>
                @foreach($catalog as $key => $entry)
                <option value="{{ $key }}" data-price="{{ $entry['price'] }}">{{ $entry['label'] }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="any" name="items[__i__][quantity]" class="form-control form-control-sm item-qty" value="1"></td>
        <td><input type="number" step="any" name="items[__i__][unit_price]" class="form-control form-control-sm item-price" value="0"></td>
        <td><input type="number" step="any" name="items[__i__][discount]" class="form-control form-control-sm item-discount" value="0"></td>
        <td>
            <select name="items[__i__][employee_id]" class="form-select form-select-sm">
                <option value="">-</option>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                @endforeach
            </select>
        </td>
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

    function addRow() {
        const html = template.replace(/__i__/g, index++);
        body.insertAdjacentHTML('beforeend', html);
        recalc();
    }

    document.getElementById('add-row').addEventListener('click', addRow);

    body.addEventListener('input', recalc);
    document.getElementById('discount').addEventListener('input', recalc);
    document.getElementById('tax').addEventListener('input', recalc);

    body.addEventListener('change', function (event) {
        if (event.target.classList.contains('item-ref')) {
            const option = event.target.selectedOptions[0];
            const row = event.target.closest('tr');
            row.querySelector('.item-price').value = option ? (option.dataset.price || 0) : 0;
            recalc();
        }
    });

    body.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
            recalc();
        }
    });

    addRow();
})();
</script>
@endpush
