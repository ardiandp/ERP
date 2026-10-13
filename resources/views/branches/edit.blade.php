@extends('layouts.app')
@section('content')
<h4>Edit Branch</h4>
<form method="POST" action="{{ route('branches.update', $branch) }}" class="card card-body">
    @method('PUT')
    @include('branches._form')
</form>
@endsection
