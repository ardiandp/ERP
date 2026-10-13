@extends('layouts.app')
@section('content')
<h4>Tambah {{ $cfg['title'] }}</h4>
<form method="POST" action="{{ route('masterdata.store', $entity) }}" class="card card-body">
    @include('masterdata._form')
</form>
@endsection
