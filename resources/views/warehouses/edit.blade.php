@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Gudang</h4>
<form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
    @method('PUT')
    @include('warehouses._form')
</form>
@endsection
