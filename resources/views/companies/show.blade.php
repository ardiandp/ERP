@extends('layouts.app')
@section('content')
<h4>{{ $company->name }}</h4>
<table class="table table-bordered">
    <tr><th>Code</th><td>{{ $company->code }}</td></tr>
    <tr><th>Legal Name</th><td>{{ $company->legal_name }}</td></tr>
    <tr><th>NPWP</th><td>{{ $company->tax_number }}</td></tr>
    <tr><th>Phone</th><td>{{ $company->phone }}</td></tr>
    <tr><th>Email</th><td>{{ $company->email }}</td></tr>
    <tr><th>Alamat</th><td>{{ $company->address }}</td></tr>
    <tr><th>Status</th><td>{{ $company->status }}</td></tr>
</table>
<a href="{{ route('companies.edit', $company) }}" class="btn btn-secondary btn-sm">Edit</a>
@endsection
