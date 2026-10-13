@extends('layouts.app')
@section('content')
<h4 class="mb-3">Retur Invoice {{ $transaction->invoice_number }}</h4>
<form method="POST" action="{{ route('sales.returns.store', $transaction) }}">
    @csrf
    <div class="row g-3 mb-3">
        <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" name="return_date" class="form-control" value="{{ now()->toDateString() }}"></div>
        <div class="col-md-3">
            <label class="form-label">Warehouse (restock)</label>
            <select name="warehouse_id" class="form-select">
                <option value="">Default</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Alasan</label><input name="reason" class="form-control"></div>
    </div>
    <table class="table table-striped">
        <thead><tr><th>Item</th><th>Type</th><th class="text-end">Qty Beli</th><th class="text-end">Harga</th><th style="width:15%">Qty Retur</th></tr></thead>
        <tbody>
            @foreach($transaction->items as $i => $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->item_type }}</td>
                <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
                <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td>
                    <input type="hidden" name="items[{{ $i }}][sales_transaction_item_id]" value="{{ $item->id }}">
                    <input type="number" step="any" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" value="0">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan Retur</button>
</form>
@endsection
