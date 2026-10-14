@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Aset</h4>
<form method="POST" action="{{ route('assets.store') }}">
    @include('assets._form')
</form>
@endsection
