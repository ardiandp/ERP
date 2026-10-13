@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Payroll Periods</h4>
    <a href="{{ route('payroll-periods.create') }}" class="btn btn-primary btn-sm">Tambah Periode</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Periode</th><th>Payment Date</th><th>Status</th><th>Payroll</th><th></th></tr></thead>
    <tbody>
        @forelse($periods as $period)
        <tr>
            <td>{{ $period->code }}</td>
            <td>{{ $period->name }}</td>
            <td>{{ $period->period_start?->format('d/m/Y') }} - {{ $period->period_end?->format('d/m/Y') }}</td>
            <td>{{ $period->payment_date?->format('d/m/Y') ?? '-' }}</td>
            <td><span class="badge bg-{{ $period->status === 'CLOSED' ? 'secondary' : 'success' }}">{{ $period->status }}</span></td>
            <td>{{ $period->payrolls_count }}</td>
            <td><a href="{{ route('payroll-periods.show', $period) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada periode.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $periods->links() }}
@endsection
