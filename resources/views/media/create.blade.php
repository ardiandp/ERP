@extends('layouts.app')
@section('content')
<h4 class="mb-3">Unggah File</h4>
<form method="POST" action="{{ route('media.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">File</label><input type="file" name="file" class="form-control" required></div>
        <div class="col-md-3 mb-3"><label class="form-label">Koleksi</label><input name="collection_name" class="form-control" value="{{ old('collection_name') }}"></div>
        <div class="col-md-3 mb-3"><label class="form-label">Lampirkan Ke</label>
            <select name="attachable_type" class="form-select">
                <option value="">- tidak dilampirkan -</option>
                <option value="asset" @selected(old('attachable_type') === 'asset')>Asset</option>
                <option value="employee" @selected(old('attachable_type') === 'employee')>Employee</option>
            </select>
        </div>
        <div class="col-md-3 mb-3"><label class="form-label">ID Terkait</label><input name="attachable_id" class="form-control" value="{{ old('attachable_id') }}"></div>
    </div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <button class="btn btn-primary">Unggah</button>
</form>
@endsection
