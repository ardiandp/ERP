@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Expense {{ $expense->expense_number }}</h4>
    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>Kategori:</strong> {{ $expense->category?->name }}</div>
            <div class="col-md-4"><strong>Cabang:</strong> {{ $expense->branch?->name }}</div>
            <div class="col-md-4"><strong>Supplier:</strong> {{ $expense->supplier?->name }}</div>
            <div class="col-md-4"><strong>Akun:</strong> {{ $expense->financialAccount?->account_name }}</div>
            <div class="col-md-4"><strong>Jumlah:</strong> {{ number_format($expense->amount, 2) }}</div>
            <div class="col-md-4"><strong>Tanggal:</strong> {{ $expense->expense_date?->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ $expense->status }}</div>
            <div class="col-md-8"><strong>Deskripsi:</strong> {{ $expense->description }}</div>
        </div>
    </div>
</div>
@if($expense->status === 'DRAFT')
<form method="POST" action="{{ route('expenses.approve', $expense) }}">
    @csrf @method('PATCH')
    <button class="btn btn-success">Setujui</button>
</form>
@endif
@endsection
