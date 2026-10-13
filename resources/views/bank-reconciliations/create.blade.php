@extends('layouts.app')
@section('content')
<h4 class="mb-3">Rekonsiliasi Mutasi Bank</h4>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('bank-reconciliations.store') }}">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Mutasi Bank</label>
            <select name="bank_transaction_id" class="form-select">
                <option value="">-- pilih --</option>
                @foreach($bankTransactions as $bt)
                <option value="{{ $bt->id }}" @selected((string) old('bank_transaction_id', $bankTransaction?->id ?? '') === (string) $bt->id)>
                    {{ $bt->transaction_date?->format('d/m/Y') }} - {{ $bt->financialAccount?->account_name }} - {{ $bt->transaction_type }} {{ number_format($bt->amount, 2) }} - {{ $bt->reference_number }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3"><label class="form-label">Payment</label>
            <select name="payment_id" class="form-select">
                <option value="">-- pilih --</option>
                @foreach($payments as $payment)
                <option value="{{ $payment->id }}" @selected((string) old('payment_id') === (string) $payment->id)>{{ $payment->payment_number }} - {{ number_format($payment->amount, 2) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Matched Amount (opsional)</label><input type="number" step="0.01" name="matched_amount" class="form-control" value="{{ old('matched_amount') }}"></div>
        <div class="col-md-8 mb-3"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"></div>
    </div>
    <button class="btn btn-primary">Rekonsiliasi</button>
</form>
@endsection
