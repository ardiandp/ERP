@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Periode {{ $period->name }}</h4>
    <a href="{{ route('payroll-periods.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <div><strong>Code:</strong> {{ $period->code }}</div>
            <div><strong>Periode:</strong> {{ $period->period_start?->format('d/m/Y') }} - {{ $period->period_end?->format('d/m/Y') }}</div>
            <div><strong>Status:</strong> {{ $period->status }}</div>
        </div>
        <div class="d-flex gap-2">
            @if($period->status === 'OPEN')
            <form method="POST" action="{{ route('payrolls.generate', $period) }}">
                @csrf
                <button class="btn btn-primary">Generate Payroll</button>
            </form>
            @endif
            @if($period->status !== 'CLOSED')
            <form method="POST" action="{{ route('payroll-periods.close', $period) }}">
                @csrf @method('PATCH')
                <button class="btn btn-outline-secondary">Tutup Periode</button>
            </form>
            @endif
        </div>
    </div>
</div>
<table class="table table-striped">
    <thead><tr><th>Karyawan</th><th class="text-end">Basic</th><th class="text-end">Earnings</th><th class="text-end">Deductions</th><th class="text-end">Net</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($period->payrolls as $payroll)
        <tr>
            <td>{{ $payroll->employee?->full_name }}</td>
            <td class="text-end">{{ number_format($payroll->basic_salary, 2) }}</td>
            <td class="text-end">{{ number_format($payroll->total_earnings, 2) }}</td>
            <td class="text-end">{{ number_format($payroll->total_deductions, 2) }}</td>
            <td class="text-end fw-bold">{{ number_format($payroll->net_salary, 2) }}</td>
            <td>{{ $payroll->status }}</td>
            <td><a href="{{ route('payrolls.show', $payroll) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada payroll. Klik Generate Payroll.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
