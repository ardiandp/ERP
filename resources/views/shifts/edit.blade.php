@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Shift</h4>
<form method="POST" action="{{ route('shifts.update', $shift) }}">
    @method('PUT')
    @include('shifts._form')
</form>
@endsection
