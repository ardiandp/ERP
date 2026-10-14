@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Akun Digital</h4>
<form method="POST" action="{{ route('digital-accounts.store') }}">
    @include('digital-accounts._form')
</form>
@endsection
