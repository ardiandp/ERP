@extends('layouts.app')
@section('content')
<h4>{{ $branch->name }}</h4>
<table class="table table-bordered">
    <tr><th>Company</th><td>{{ $branch->company->name ?? '-' }}</td></tr>
    <tr><th>Code</th><td>{{ $branch->code }}</td></tr>
    <tr><th>Tipe</th><td>{{ $branch->branch_type }}</td></tr>
    <tr><th>Phone</th><td>{{ $branch->phone }}</td></tr>
    <tr><th>Email</th><td>{{ $branch->email }}</td></tr>
    <tr><th>Alamat</th><td>{{ $branch->address }}</td></tr>
    <tr><th>Status</th><td>{{ $branch->status }}</td></tr>
</table>
<a href="{{ route('branches.edit', $branch) }}" class="btn btn-secondary btn-sm">Edit</a>
@endsection
