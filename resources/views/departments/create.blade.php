@extends('layouts.app')
@section('content')
<h4>Tambah Department</h4>
<form method="POST" action="{{ route('departments.store') }}" class="card card-body">
    @include('departments._form')
</form>
@endsection
