@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Periode Payroll</h4>
<form method="POST" action="{{ route('payroll-periods.store') }}">
    @csrf
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code') }}"></div>
        <div class="col-md-8 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name') }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Periode Mulai</label><input type="date" name="period_start" class="form-control" value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Periode Akhir</label><input type="date" name="period_end" class="form-control" value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Tanggal Bayar</label><input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}"></div>
    </div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
