@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Expense</h4>
<form method="POST" action="{{ route('expenses.store') }}">
    @csrf
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Kategori</label>
            <select name="expense_category_id" class="form-select">
                <option value="">-- pilih --</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('expense_category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Cabang</label>
            <select name="branch_id" class="form-select">
                <option value="">-</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select">
                <option value="">-</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Akun Pembayaran</label>
            <select name="financial_account_id" class="form-select">
                <option value="">-</option>
                @foreach($accounts as $account)
                <option value="{{ $account->id }}" @selected((string) old('financial_account_id') === (string) $account->id)>{{ $account->account_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Jumlah</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Tanggal</label><input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', now()->toDateString()) }}"></div>
        <div class="col-md-12 mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control">{{ old('description') }}</textarea></div>
    </div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
