@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Digital Accounts</h4>
    <a href="{{ route('digital-accounts.create') }}" class="btn btn-primary btn-sm">Tambah Akun</a>
</div>
<table class="table table-striped">
    <thead><tr><th>Category</th><th>Name</th><th>Username</th><th>Email</th><th>Renewal</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($accounts as $account)
        <tr>
            <td>{{ $account->category?->name }}</td>
            <td>{{ $account->name }}</td>
            <td>{{ $account->username ?? '-' }}</td>
            <td>{{ $account->email ?? '-' }}</td>
            <td>{{ $account->renewal_date?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $account->status }}</td>
            <td>
                <a href="{{ route('digital-accounts.edit', $account) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('digital-accounts.destroy', $account) }}" class="d-inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Belum ada akun digital.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $accounts->links() }}
@endsection
