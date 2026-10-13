@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Payroll {{ $payroll->employee?->full_name }} - {{ $payroll->period?->name }}</h4>
    <a href="{{ route('payrolls.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">Komponen Gaji</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Komponen</th><th>Type</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    @foreach($payroll->items as $item)
                    <tr>
                        <td>{{ $item->component_name }}</td>
                        <td>{{ $item->type }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="2">Total Earnings</th><th class="text-end">{{ number_format($payroll->total_earnings, 2) }}</th></tr>
                    <tr><th colspan="2">Total Deductions</th><th class="text-end">{{ number_format($payroll->total_deductions, 2) }}</th></tr>
                    <tr><th colspan="2">Net Salary</th><th class="text-end">{{ number_format($payroll->net_salary, 2) }}</th></tr>
                </tfoot>
            </table>
        </div>

        @if($payroll->status !== 'APPROVED' && $payroll->status !== 'PAID')
        <div class="card mb-3">
            <div class="card-header">Tambah Adjustment</div>
            <div class="card-body">
                <form method="POST" action="{{ route('payrolls.adjust', $payroll) }}" class="row g-2">
                    @csrf
                    <div class="col-md-2">
                        <select name="type" class="form-select form-select-sm">
                            <option value="EARNING">EARNING</option>
                            <option value="DEDUCTION">DEDUCTION</option>
                        </select>
                    </div>
                    <div class="col-md-4"><input name="description" class="form-control form-control-sm" placeholder="Deskripsi"></div>
                    <div class="col-md-2"><input type="number" step="0.01" name="amount" class="form-control form-control-sm" placeholder="Jumlah"></div>
                    <div class="col-md-3"><input name="reason" class="form-control form-control-sm" placeholder="Alasan"></div>
                    <div class="col-md-1"><button class="btn btn-sm btn-primary">Tambah</button></div>
                </form>
            </div>
        </div>
        @endif

        @if($payroll->adjustments->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">Adjustments</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Type</th><th>Deskripsi</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    @foreach($payroll->adjustments as $adjustment)
                    <tr>
                        <td>{{ $adjustment->type }}</td>
                        <td>{{ $adjustment->description }}</td>
                        <td class="text-end">{{ number_format($adjustment->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Aksi ({{ $payroll->status }})</div>
            <div class="card-body d-grid gap-2">
                @if($payroll->status === 'DRAFT')
                <form method="POST" action="{{ route('payrolls.approve', $payroll) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-success w-100">Setujui</button>
                </form>
                @endif

                @if($payroll->status === 'APPROVED' && !$payroll->payslip)
                <form method="POST" action="{{ route('payrolls.payslip', $payroll) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="template_id" class="form-select form-select-sm">
                            <option value="">Template default</option>
                            @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Buat Slip Gaji</button>
                </form>
                @endif

                @if($payroll->payslip)
                <a href="{{ route('payslips.show', $payroll->payslip) }}" class="btn btn-outline-primary w-100">Lihat Slip Gaji</a>
                @endif

                @if($payroll->status === 'APPROVED')
                <form method="POST" action="{{ route('payrolls.pay', $payroll) }}">
                    @csrf
                    <div class="mb-2">
                        <select name="payment_method_id" class="form-select form-select-sm">
                            @foreach($methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="financial_account_id" class="form-select form-select-sm">
                            <option value="">- akun -</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-success w-100">Bayar</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
