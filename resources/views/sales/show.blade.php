@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Invoice {{ $transaction->invoice_number }}</h4>
    <div>
        <a href="{{ route('sales.returns.create', $transaction) }}" class="btn btn-outline-secondary btn-sm">Retur</a>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>Tanggal</th><td>{{ optional($transaction->transaction_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Cabang</th><td>{{ $transaction->branch?->name }}</td></tr>
            <tr><th>Customer</th><td>{{ $transaction->customer?->name ?? 'Umum' }}</td></tr>
            <tr><th>Status</th><td>{{ $transaction->status }}</td></tr>
        </table>
    </div>
</div>

<table class="table table-striped">
    <thead><tr><th>Item</th><th>Type</th><th>Terapis</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Diskon</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
        @foreach($transaction->items as $item)
        <tr>
            <td>{{ $item->item_name }}</td>
            <td>{{ $item->item_type }}</td>
            <td>{{ $item->employee?->full_name }}</td>
            <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->discount, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><th colspan="6" class="text-end">Subtotal</th><td class="text-end">{{ number_format((float) $transaction->subtotal, 2) }}</td></tr>
        <tr><th colspan="6" class="text-end">Diskon</th><td class="text-end">{{ number_format((float) $transaction->discount, 2) }}</td></tr>
        <tr><th colspan="6" class="text-end">Pajak</th><td class="text-end">{{ number_format((float) $transaction->tax, 2) }}</td></tr>
        <tr><th colspan="6" class="text-end">Grand Total</th><td class="text-end fw-bold">{{ number_format((float) $transaction->grand_total, 2) }}</td></tr>
    </tfoot>
</table>

<div class="row">
    <div class="col-md-6">
        <h5>Pembayaran</h5>
        <table class="table table-sm">
            <thead><tr><th>No</th><th>Metode</th><th>Tanggal</th><th class="text-end">Jumlah</th></tr></thead>
            <tbody>
                @forelse($transaction->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_number }}</td>
                    <td>{{ $payment->paymentMethod?->name }}</td>
                    <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                    <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total Bayar</th><td class="text-end">{{ number_format((float) $transaction->payments->sum('amount'), 2) }}</td></tr></tfoot>
        </table>
    </div>
    <div class="col-md-6">
        <h5>Tambah Pembayaran</h5>
        <form method="POST" action="{{ route('sales.payments.store', $transaction) }}" class="row g-2">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Metode</label>
                <select name="payment_method_id" class="form-select">
                    <option value="">-</option>
                    @foreach($paymentMethods as $method)
                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jumlah</label>
                <input type="number" step="any" name="amount" class="form-control" value="{{ old('amount', $transaction->grand_total - $transaction->payments->sum('amount')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal</label>
                <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Referensi</label>
                <input name="reference_number" class="form-control">
            </div>
            @if ($errors->any())<div class="col-12"><div class="alert alert-danger">{{ $errors->first() }}</div></div>@endif
            <div class="col-12"><button class="btn btn-primary btn-sm">Catat Pembayaran</button></div>
        </form>
    </div>
</div>
@endsection
