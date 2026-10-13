@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Payrolls</h4>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="payroll_period_id" class="form-select form-select-sm">
            <option value="">Semua Periode</option>
            @foreach($periods as $period)
            <option value="{{ $period->id }}" @selected((string) request('payroll_period_id') === (string) $period->id)>{{ $period->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            @foreach(['DRAFT', 'APPROVED', 'PAID'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Karyawan</th><th>Periode</th><th class="text-end">Net Salary</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($payrolls as $payroll)
        <tr>
            <td>{{ $payroll->employee?->full_name }}</td>
            <td>{{ $payroll->period?->name }}</td>
            <td class="text-end">{{ number_format($payroll->net_salary, 2) }}</td>
            <td>{{ $payroll->status }}</td>
            <td><a href="{{ route('payrolls.show', $payroll) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="5">Belum ada payroll.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $payrolls->links() }}
@endsection
