@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Bills</h4>
    <a href="{{ route('bills.generate') }}" class="btn btn-primary btn-sm">Generate Tagihan</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            @foreach(['UNPAID', 'PAID', 'OVERDUE'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Number</th><th>Template</th><th>Period</th><th>Due</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($bills as $bill)
        <tr>
            <td>{{ $bill->bill_number }}</td>
            <td>{{ $bill->template?->name }}</td>
            <td>{{ $bill->period_start?->format('d/m/Y') }} - {{ $bill->period_end?->format('d/m/Y') }}</td>
            <td>{{ $bill->due_date?->format('d/m/Y') }}</td>
            <td class="text-end">{{ number_format($bill->total_amount, 2) }}</td>
            <td><span class="badge bg-{{ $bill->status === 'PAID' ? 'success' : ($bill->status === 'OVERDUE' ? 'danger' : 'warning') }}">{{ $bill->status }}</span></td>
            <td><a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada tagihan.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $bills->links() }}
@endsection
