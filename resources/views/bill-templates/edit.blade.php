@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Template Tagihan</h4>
<form method="POST" action="{{ route('bill-templates.update', $template) }}">
    @method('PUT')
    @include('bill-templates._form')
</form>
@endsection
