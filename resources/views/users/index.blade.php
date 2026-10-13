@extends('layouts.app')
@section('content')
<h4>Users</h4>
<table class="table table-striped">
    <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Roles</th><th>Last Login</th></tr></thead>
    <tbody>
        @forelse($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->status }}</td>
            <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
            <td>{{ $user->last_login_at }}</td>
        </tr>
        @empty
        <tr><td colspan="5">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $users->links() }}
@endsection
