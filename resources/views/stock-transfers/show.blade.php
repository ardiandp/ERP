@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Transfer {{ $transfer->transfer_number }}</h4>
    <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>Dari</th><td>{{ $transfer->fromWarehouse?->name }}</td></tr>
            <tr><th>Ke</th><td>{{ $transfer->toWarehouse?->name }}</td></tr>
            <tr><th>Tanggal</th><td>{{ optional($transfer->transfer_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Status</th><td>{{ $transfer->status }}</td></tr>
            <tr><th>Catatan</th><td>{{ $transfer->notes }}</td></tr>
        </table>
        @if($transfer->status === 'DRAFT')
        <form method="POST" action="{{ route('stock-transfers.approve', $transfer) }}">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm">Setujui & Pindahkan Stok</button>
        </form>
        @endif
    </div>
</div>

@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<table class="table table-striped">
    <thead><tr><th>Product</th><th class="text-end">Qty</th></tr></thead>
    <tbody>
        @foreach($transfer->items as $item)
        <tr>
            <td>{{ $item->product?->name }}</td>
            <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
