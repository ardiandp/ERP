@extends('layouts.app')
@section('content')
<h4>Edit Company</h4>
<form method="POST" action="{{ route('companies.update', $company) }}" class="card card-body">
    @method('PUT')
    @include('companies._form')
</form>
@endsection
