@extends('layouts.app')
@section('content')
<h4>Edit Employee</h4>
<form method="POST" action="{{ route('employees.update', $employee) }}" class="card card-body">
    @method('PUT')
    @include('employees._form')
</form>
@endsection
