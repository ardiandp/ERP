@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Template Tagihan</h4>
<form method="POST" action="{{ route('bill-templates.store') }}">
    @include('bill-templates._form')
</form>
@endsection
