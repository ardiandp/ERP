@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Aset</h4>
<form method="POST" action="{{ route('assets.update', $asset) }}">
    @csrf @method('PUT')
    @include('assets._form')
</form>
@endsection
