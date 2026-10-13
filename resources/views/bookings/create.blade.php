@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Booking</h4>
<form method="POST" action="{{ route('bookings.store') }}">
    @include('bookings._form')
</form>
@endsection
