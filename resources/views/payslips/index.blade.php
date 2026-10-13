@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Payslips</h4>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            @foreach(['DRAFT', 'PUBLISHED', 'VOID'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Number</th><th>Karyawan</th><th>Periode</th><th class="text-end">Net</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($payslips as $payslip)
        <tr>
            <td>{{ $payslip->payslip_number }}</td>
            <td>{{ $payslip->employee?->full_name }}</td>
            <td>{{ $payslip->payroll?->period?->name }}</td>
            <td class="text-end">{{ number_format($payslip->net_salary, 2) }}</td>
            <td><span class="badge bg-{{ $payslip->status === 'PUBLISHED' ? 'success' : 'secondary' }}">{{ $payslip->status }}</span></td>
            <td><a href="{{ route('payslips.show', $payslip) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="6">Belum ada slip gaji.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $payslips->links() }}
@endsection
