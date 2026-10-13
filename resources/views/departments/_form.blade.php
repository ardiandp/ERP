@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Company</label>
        <select name="company_id" class="form-select">
            @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected(old('company_id', $department->company_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $department->code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $department->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $department->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $department->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
