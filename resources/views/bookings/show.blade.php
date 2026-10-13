@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Booking {{ $booking->booking_number }}</h4>
    <div>
        <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>Customer</th><td>{{ $booking->customer?->name }}</td></tr>
            <tr><th>Branch</th><td>{{ $booking->branch?->name }}</td></tr>
            <tr><th>Tanggal</th><td>{{ optional($booking->booking_date)->format('Y-m-d') }}</td></tr>
            <tr><th>Waktu</th><td>{{ $booking->start_time }} - {{ $booking->end_time }}</td></tr>
            <tr><th>Status</th><td>{{ $booking->status }}</td></tr>
            <tr><th>Catatan</th><td>{{ $booking->notes }}</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h5>Ubah Status</h5>
        <form method="POST" action="{{ route('bookings.status', $booking) }}" class="row g-2 mb-3">
            @csrf @method('PATCH')
            <div class="col-md-6">
                <select name="status" class="form-select">
                    @foreach(['BOOKED', 'CONFIRMED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'] as $status)
                    <option value="{{ $status }}" @selected($booking->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><button class="btn btn-primary">Update</button></div>
        </form>

        <h5>Tambah Item</h5>
        <form method="POST" action="{{ route('bookings.items.store', $booking) }}" class="row g-2">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Layanan</label>
                <select name="service_id" class="form-select">
                    <option value="">-</option>
                    @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Paket</label>
                <select name="package_id" class="form-select">
                    <option value="">-</option>
                    @foreach($packages as $package)
                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Terapis</label>
                <select name="employee_id" class="form-select">
                    <option value="">-</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mulai</label>
                <input type="datetime-local" name="scheduled_start" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Selesai</label>
                <input type="datetime-local" name="scheduled_end" class="form-control">
            </div>
            <div class="col-12"><button class="btn btn-primary btn-sm">Tambah</button></div>
        </form>
    </div>
</div>

@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<table class="table table-striped">
    <thead><tr><th>Layanan</th><th>Paket</th><th>Terapis</th><th>Jadwal</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($booking->items as $item)
        <tr>
            <td>{{ $item->service?->name }}</td>
            <td>{{ $item->package?->name }}</td>
            <td>{{ $item->employee?->full_name }}</td>
            <td>{{ $item->scheduled_start }} - {{ $item->scheduled_end }}</td>
            <td>{{ $item->status }}</td>
            <td>
                <form method="POST" action="{{ route('bookings.items.destroy', [$booking, $item]) }}" class="d-inline" onsubmit="return confirm('Hapus item?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada item.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
