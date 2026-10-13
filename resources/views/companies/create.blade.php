@extends('layouts.app')
@section('content')
<h4>Tambah Company</h4>
<form method="POST" action="{{ route('companies.store') }}" class="card card-body">
    @include('companies._form')
</form>
@endsection
