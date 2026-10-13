@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Bookings</h4>
    <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">Tambah</a>
</div>
<form class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua Status</option>
            @foreach(['BOOKED', 'CONFIRMED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>No</th><th>Date</th><th>Customer</th><th>Branch</th><th>Time</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($bookings as $booking)
        <tr>
            <td>{{ $booking->booking_number }}</td>
            <td>{{ optional($booking->booking_date)->format('Y-m-d') }}</td>
            <td>{{ $booking->customer?->name }}</td>
            <td>{{ $booking->branch?->name }}</td>
            <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
            <td>{{ $booking->status }}</td>
            <td><a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada booking.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $bookings->links() }}
@endsection
