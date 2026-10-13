@extends('layouts.app')
@section('content')
<h4>Tambah Position</h4>
<form method="POST" action="{{ route('positions.store') }}" class="card card-body">
    @include('positions._form')
</form>
@endsection
