@extends('layouts.app')
@section('content')
<h4 class="mb-3">Catat Mutasi Bank</h4>
<form method="POST" action="{{ route('bank-transactions.store') }}">
    @csrf
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Akun</label>
            <select name="financial_account_id" class="form-select">
                @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected((string) old('financial_account_id') === (string) $account->id)>{{ $account->account_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Tanggal</label><input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', now()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Tipe</label>
            <select name="transaction_type" class="form-select">
                <option value="IN" @selected(old('transaction_type') === 'IN')>IN (Masuk)</option>
                <option value="OUT" @selected(old('transaction_type') === 'OUT')>OUT (Keluar)</option>
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Jumlah</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Reference Number</label><input name="reference_number" class="form-control" value="{{ old('reference_number') }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">External Reference</label><input name="external_reference" class="form-control" value="{{ old('external_reference') }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Saldo (opsional)</label><input type="number" step="0.01" name="balance" class="form-control" value="{{ old('balance') }}"></div>
        <div class="col-md-8 mb-3"><label class="form-label">Deskripsi</label><input name="description" class="form-control" value="{{ old('description') }}"></div>
    </div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
