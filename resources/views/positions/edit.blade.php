@extends('layouts.app')
@section('content')
<h4>Edit Position</h4>
<form method="POST" action="{{ route('positions.update', $position) }}" class="card card-body">
    @method('PUT')
    @include('positions._form')
</form>
@endsection
