@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Customer</label>
        <select name="customer_id" class="form-select">
            <option value="">-</option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}" @selected((string) old('customer_id', $booking->customer_id ?? '') === (string) $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $booking->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Tanggal</label><input type="date" name="booking_date" class="form-control" value="{{ old('booking_date', optional($booking->booking_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Mulai</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', isset($booking) ? substr((string) $booking->start_time, 0, 5) : '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Selesai</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', isset($booking) ? substr((string) $booking->end_time, 0, 5) : '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['BOOKED', 'CONFIRMED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'] as $status)
            <option value="{{ $status }}" @selected(old('status', $booking->status ?? 'BOOKED') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12 mb-3"><label class="form-label">Catatan</label><textarea name="notes" class="form-control">{{ old('notes', $booking->notes ?? '') }}</textarea></div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
