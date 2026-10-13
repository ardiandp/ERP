@extends('layouts.app')
@section('content')
<h4>Edit Customer</h4>
<form method="POST" action="{{ route('customers.update', $customer) }}" class="card card-body">
    @method('PUT')
    @include('customers._form')
</form>
@endsection
