@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Department</label>
        <select name="department_id" class="form-select">
            @foreach($departments as $d)
            <option value="{{ $d->id }}" @selected(old('department_id', $position->department_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $position->code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $position->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $position->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $position->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
