@extends('layouts.app')
@section('content')
<h4>Tambah Branch</h4>
<form method="POST" action="{{ route('branches.store') }}" class="card card-body">
    @include('branches._form')
</form>
@endsection
