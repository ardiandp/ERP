@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Tagihan {{ $bill->bill_number }}</h4>
    <a href="{{ route('bills.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>Template:</strong> {{ $bill->template?->name }}</div>
            <div class="col-md-4"><strong>Cabang:</strong> {{ $bill->branch?->name }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ $bill->status }}</div>
            <div class="col-md-4"><strong>Periode:</strong> {{ $bill->period_start?->format('d/m/Y') }} - {{ $bill->period_end?->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Jatuh Tempo:</strong> {{ $bill->due_date?->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Dibayar:</strong> {{ $bill->paid_at?->format('d/m/Y H:i') ?? '-' }}</div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4"><strong>Amount:</strong> {{ number_format($bill->amount, 2) }}</div>
            <div class="col-md-4"><strong>Denda:</strong> {{ number_format($bill->penalty_amount, 2) }}</div>
            <div class="col-md-4"><strong>Total:</strong> {{ number_format($bill->total_amount, 2) }}</div>
        </div>
    </div>
</div>
@if($bill->status !== 'PAID')
<form method="POST" action="{{ route('bills.pay', $bill) }}">
    @csrf @method('PATCH')
    <button class="btn btn-success">Tandai Lunas</button>
</form>
@endif
@endsection
