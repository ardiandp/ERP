@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Gudang</h4>
<form method="POST" action="{{ route('warehouses.store') }}">
    @include('warehouses._form')
</form>
@endsection
