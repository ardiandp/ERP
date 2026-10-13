@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Shift</h4>
<form method="POST" action="{{ route('shifts.store') }}">
    @include('shifts._form')
</form>
@endsection
