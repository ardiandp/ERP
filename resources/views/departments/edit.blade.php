@extends('layouts.app')
@section('content')
<h4>Edit Department</h4>
<form method="POST" action="{{ route('departments.update', $department) }}" class="card card-body">
    @method('PUT')
    @include('departments._form')
</form>
@endsection
