@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Opname {{ $opname->opname_number }}</h4>
    <a href="{{ route('stock-opnames.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>Warehouse</th><td>{{ $opname->warehouse?->name }}</td></tr>
            <tr><th>Tanggal</th><td>{{ optional($opname->opname_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Status</th><td>{{ $opname->status }}</td></tr>
            <tr><th>Catatan</th><td>{{ $opname->notes }}</td></tr>
        </table>
        @if($opname->status === 'DRAFT')
        <form method="POST" action="{{ route('stock-opnames.approve', $opname) }}">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm">Setujui & Sesuaikan Stok</button>
        </form>
        @endif
    </div>
</div>

@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<table class="table table-striped">
    <thead><tr><th>Product</th><th class="text-end">System</th><th class="text-end">Fisik</th><th class="text-end">Selisih</th></tr></thead>
    <tbody>
        @foreach($opname->items as $item)
        <tr>
            <td>{{ $item->product?->name }}</td>
            <td class="text-end">{{ number_format((float) $item->system_quantity, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->physical_quantity, 2) }}</td>
            <td class="text-end">{{ number_format((float) $item->variance_quantity, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
