@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Cash & Bank Accounts</h4>
</div>
<table class="table table-striped">
    <thead><tr><th>Code</th><th>Name</th><th>Type</th><th class="text-end">Opening</th><th class="text-end">In</th><th class="text-end">Out</th><th class="text-end">Balance</th></tr></thead>
    <tbody>
        @forelse($accounts as $account)
        <tr>
            <td>{{ $account->account_code }}</td>
            <td>{{ $account->account_name }}</td>
            <td>{{ $account->account_type }}</td>
            <td class="text-end">{{ number_format($account->opening_balance, 2) }}</td>
            <td class="text-end text-success">{{ number_format($account->total_in, 2) }}</td>
            <td class="text-end text-danger">{{ number_format($account->total_out, 2) }}</td>
            <td class="text-end fw-bold">{{ number_format($account->computed_balance, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada akun. Tambahkan lewat Master Data &gt; Financial Account.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
