@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Slip Gaji {{ $payslip->payslip_number }}</h4>
    <a href="{{ route('payslips.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>Karyawan:</strong> {{ $payslip->employee?->full_name }}</div>
            <div class="col-md-4"><strong>Periode:</strong> {{ $payslip->payroll?->period?->name }}</div>
            <div class="col-md-4"><strong>Tanggal:</strong> {{ $payslip->issue_date?->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ $payslip->status }}</div>
            <div class="col-md-4"><strong>Verifikasi:</strong> {{ $payslip->verification_code }}</div>
        </div>
    </div>
</div>
<table class="table table-sm">
    <thead><tr><th>Komponen</th><th>Type</th><th class="text-end">Amount</th></tr></thead>
    <tbody>
        @foreach($payslip->items as $item)
        <tr>
            <td>{{ $item->component_name }}</td>
            <td>{{ $item->type }}</td>
            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr><th colspan="2">Gross</th><th class="text-end">{{ number_format($payslip->gross_salary, 2) }}</th></tr>
        <tr><th colspan="2">Total Potongan</th><th class="text-end">{{ number_format($payslip->total_deduction, 2) }}</th></tr>
        <tr><th colspan="2">Net</th><th class="text-end">{{ number_format($payslip->net_salary, 2) }}</th></tr>
    </tfoot>
</table>
@if($payslip->status === 'DRAFT')
<form method="POST" action="{{ route('payslips.publish', $payslip) }}">
    @csrf @method('PATCH')
    <button class="btn btn-success">Publikasikan</button>
</form>
@endif
@endsection
