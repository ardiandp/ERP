@extends('layouts.app')
@section('content')
<h4>Tambah Employee</h4>
<form method="POST" action="{{ route('employees.store') }}" class="card card-body">
    @include('employees._form')
</form>
@endsection
