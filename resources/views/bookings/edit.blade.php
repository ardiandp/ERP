@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Booking {{ $booking->booking_number }}</h4>
<form method="POST" action="{{ route('bookings.update', $booking) }}">
    @method('PUT')
    @include('bookings._form')
</form>
@endsection
