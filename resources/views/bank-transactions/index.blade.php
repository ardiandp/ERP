@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Bank Mutations</h4>
    <a href="{{ route('bank-transactions.create') }}" class="btn btn-primary btn-sm">Tambah Mutasi</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="financial_account_id" class="form-select form-select-sm">
            <option value="">Semua Akun</option>
            @foreach($accounts as $account)
            <option value="{{ $account->id }}" @selected((string) request('financial_account_id') === (string) $account->id)>{{ $account->account_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <option value="UNRECONCILED" @selected(request('status') === 'UNRECONCILED')>UNRECONCILED</option>
            <option value="RECONCILED" @selected(request('status') === 'RECONCILED')>RECONCILED</option>
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">Filter</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Date</th><th>Account</th><th>Type</th><th class="text-end">Amount</th><th>Reference</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($transactions as $transaction)
        <tr>
            <td>{{ $transaction->transaction_date?->format('d/m/Y') }}</td>
            <td>{{ $transaction->financialAccount?->account_name }}</td>
            <td>{{ $transaction->transaction_type }}</td>
            <td class="text-end {{ $transaction->transaction_type === 'IN' ? 'text-success' : 'text-danger' }}">{{ number_format($transaction->amount, 2) }}</td>
            <td>{{ $transaction->reference_number }}</td>
            <td><span class="badge bg-{{ $transaction->reconciliation_status === 'RECONCILED' ? 'success' : 'secondary' }}">{{ $transaction->reconciliation_status }}</span></td>
            <td>
                @if($transaction->reconciliation_status !== 'RECONCILED')
                <a href="{{ route('bank-reconciliations.create', ['bank_transaction' => $transaction->id]) }}" class="btn btn-sm btn-outline-primary">Reconcile</a>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada mutasi.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $transactions->links() }}
@endsection
