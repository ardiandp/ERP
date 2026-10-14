@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Akun Digital</h4>
<form method="POST" action="{{ route('digital-accounts.update', $account) }}">
    @csrf @method('PUT')
    @include('digital-accounts._form')
</form>
@endsection
