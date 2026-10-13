@extends('layouts.app')
@section('content')
<h4 class="mb-3">Stock Adjustment</h4>
<form method="POST" action="{{ route('inventory.adjustment.store') }}" class="row g-3">
    @csrf
    <div class="col-md-4">
        <label class="form-label">Warehouse</label>
        <select name="warehouse_id" class="form-select">
            <option value="">-</option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-select">
            <option value="">-</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Quantity (set to)</label>
        <input type="number" step="any" name="quantity" class="form-control" value="{{ old('quantity', 0) }}">
    </div>
    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
    </div>
    @if ($errors->any())<div class="col-12"><div class="alert alert-danger">{{ $errors->first() }}</div></div>@endif
    <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
</form>
@endsection
