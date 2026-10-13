@extends('layouts.app')
@section('content')
<h4>Edit {{ $cfg['title'] }}</h4>
<form method="POST" action="{{ route('masterdata.update', [$entity, $item->id]) }}" class="card card-body">
    @method('PUT')
    @include('masterdata._form')
</form>
@endsection
