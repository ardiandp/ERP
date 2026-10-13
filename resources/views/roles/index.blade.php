@extends('layouts.app')
@section('content')
<h4>Roles</h4>
<table class="table table-striped">
    <thead><tr><th>Name</th><th>Description</th><th>Users</th><th>Permissions</th></tr></thead>
    <tbody>
        @foreach($roles as $role)
        <tr>
            <td>{{ $role->name }}</td>
            <td>{{ $role->description }}</td>
            <td>{{ $role->users_count }}</td>
            <td>{{ $role->permissions_count }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
