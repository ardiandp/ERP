@extends('layouts.app')
@section('title', 'Jadwal & Kalender Reservasi Treatment')
@section('content')
@php
    $carbonDate = \Carbon\Carbon::parse($date);
    $prevDate = $carbonDate->copy()->subDay()->toDateString();
    $nextDate = $carbonDate->copy()->addDay()->toDateString();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="mb-0">Jadwal &amp; Kalender Reservasi Treatment</h4>
        <span class="text-muted small">Kelola ketersediaan jam layanan, alokasi kamar &amp; jadwal terapis harian</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
            <i class="mdi mdi-calendar-plus me-1"></i> Tambah Booking Baru
        </a>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-view-list me-1"></i> Daftar Tabel
        </a>
    </div>
</div>

{{-- FILTER & DATE NAVIGATOR --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('bookings.schedule') }}" class="row g-2 align-items-center">
            <div class="col-md-5 d-flex align-items-center gap-1">
                <a href="{{ route('bookings.schedule', ['date' => $prevDate, 'branch_id' => $branchId]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-chevron-left"></i>
                </a>
                <input type="date" name="date" class="form-control form-control-sm text-center fw-bold" value="{{ $date }}" onchange="this.form.submit()">
                <a href="{{ route('bookings.schedule', ['date' => $nextDate, 'branch_id' => $branchId]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-chevron-right"></i>
                </a>
                <a href="{{ route('bookings.schedule', ['date' => now()->toDateString(), 'branch_id' => $branchId]) }}" class="btn btn-sm btn-outline-primary ms-1">
                    Hari Ini
                </a>
            </div>
            <div class="col-md-4">
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($branchId === $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 text-end text-muted small">
                <strong>{{ $carbonDate->translatedFormat('l, d F Y') }}</strong>
            </div>
        </form>
    </div>
</div>

{{-- SUMMARY METRICS --}}
<div class="row g-2 mb-3">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Total Reservasi</span>
            <h4 class="fw-bold mb-0 text-dark">{{ $summary['total'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Booked (Baru)</span>
            <h4 class="fw-bold mb-0 text-secondary">{{ $summary['booked'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Confirmed</span>
            <h4 class="fw-bold mb-0 text-primary">{{ $summary['confirmed'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">In Progress</span>
            <h4 class="fw-bold mb-0 text-warning">{{ $summary['in_progress'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Completed</span>
            <h4 class="fw-bold mb-0 text-success">{{ $summary['completed'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card bg-light border-0 text-center p-2">
            <span class="text-muted small">Cancelled</span>
            <h4 class="fw-bold mb-0 text-danger">{{ $summary['cancelled'] }}</h4>
        </div>
    </div>
</div>

{{-- TIMELINE SCHEDULE --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Timeline Reservasi Hari Ini</h5>
        <span class="badge bg-primary">{{ $bookings->count() }} Jadwal</span>
    </div>
    <div class="card-body">
        @if($bookings->isEmpty())
        <div class="text-center py-5">
            <i class="mdi mdi-calendar-blank fs-1 text-muted d-block mb-2"></i>
            <h5 class="text-muted">Belum ada reservasi pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.</h5>
            <p class="text-muted small">Klik tombol di bawah untuk membuat reservasi pelanggan baru.</p>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
                <i class="mdi mdi-calendar-plus me-1"></i> Buat Booking Baru
            </a>
        </div>
        @else
        <div class="row g-3">
            @foreach($bookings as $booking)
            <div class="col-lg-6">
                @php
                    $borderClass = match($booking->status) {
                        'BOOKED' => 'border-secondary',
                        'CONFIRMED' => 'border-primary',
                        'IN_PROGRESS' => 'border-warning',
                        'COMPLETED' => 'border-success',
                        'CANCELLED' => 'border-danger',
                        default => 'border-secondary',
                    };
                    $badgeClass = match($booking->status) {
                        'BOOKED' => 'bg-secondary',
                        'CONFIRMED' => 'bg-primary',
                        'IN_PROGRESS' => 'bg-warning text-dark',
                        'COMPLETED' => 'bg-success',
                        'CANCELLED' => 'bg-danger',
                        default => 'bg-secondary',
                    };
                @endphp
                <div class="card border {{ $borderClass }} h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-light text-dark border me-1">
                                    <i class="mdi mdi-clock-outline me-1"></i>
                                    {{ $booking->start_time ? substr($booking->start_time, 0, 5) : '00:00' }}
                                    @if($booking->end_time)
                                    - {{ substr($booking->end_time, 0, 5) }}
                                    @endif
                                </span>
                                <span class="badge {{ $badgeClass }}">{{ $booking->status }}</span>
                            </div>
                            <small class="text-muted fw-bold">{{ $booking->booking_number }}</small>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <div class="fw-bold fs-6 text-dark">{{ $booking->customer?->name }}</div>
                                <div class="text-muted small">
                                    <i class="mdi mdi-phone me-1"></i>{{ $booking->customer?->phone ?? '-' }}
                                </div>
                                <div class="text-muted small">
                                    <i class="mdi mdi-store me-1"></i>{{ $booking->branch?->name }}
                                </div>
                            </div>
                            <div class="col-5 text-end">
                                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary mb-1 d-block">
                                    <i class="mdi mdi-eye"></i> Detail
                                </a>
                                @if(in_array($booking->status, ['CONFIRMED', 'COMPLETED']))
                                <a href="{{ route('sales.create', ['customer_id' => $booking->customer_id, 'booking_id' => $booking->id]) }}" class="btn btn-sm btn-success d-block">
                                    <i class="mdi mdi-point-of-sale"></i> POS Kasir
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- ITEMS TREATMENT & TERAPIS --}}
                        <div class="bg-light p-2 rounded mb-2">
                            <strong class="small text-uppercase text-muted d-block mb-1">Layanan / Paket Treatment:</strong>
                            <ul class="list-unstyled mb-0 small">
                                @forelse($booking->items as $item)
                                <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span>
                                        <i class="mdi mdi-spa text-info me-1"></i>
                                        {{ $item->service?->name ?? $item->package?->name ?? 'Treatment' }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="mdi mdi-account-circle me-1"></i>
                                        <strong>{{ $item->employee?->full_name ?? 'Terapis belum ditentukan' }}</strong>
                                    </span>
                                </li>
                                @empty
                                <li class="text-muted fst-italic">Belum ada item treatment ditambahkan.</li>
                                @endforelse
                            </ul>
                        </div>

                        @if($booking->notes)
                        <div class="small text-muted mb-2">
                            <i class="mdi mdi-note-text-outline me-1"></i><em>{{ $booking->notes }}</em>
                        </div>
                        @endif

                        {{-- QUICK UPDATE STATUS FORM --}}
                        <form method="POST" action="{{ route('bookings.status', $booking) }}" class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                            @csrf @method('PATCH')
                            <label class="form-label small text-muted mb-0">Ubah Status:</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="BOOKED" @selected($booking->status === 'BOOKED')>BOOKED</option>
                                <option value="CONFIRMED" @selected($booking->status === 'CONFIRMED')>CONFIRMED</option>
                                <option value="IN_PROGRESS" @selected($booking->status === 'IN_PROGRESS')>IN PROGRESS</option>
                                <option value="COMPLETED" @selected($booking->status === 'COMPLETED')>COMPLETED</option>
                                <option value="CANCELLED" @selected($booking->status === 'CANCELLED')>CANCELLED</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
