@extends('layouts.app')
@section('content')
<h4>Tambah Customer</h4>
<form method="POST" action="{{ route('customers.store') }}" class="card card-body">
    @include('customers._form')
</form>
@endsection
