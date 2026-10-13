@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Company</label>
        <select name="company_id" class="form-select">
            @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected(old('company_id', $branch->company_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $branch->code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Nama</label><input name="name" class="form-control" value="{{ old('name', $branch->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Tipe</label><input name="branch_type" class="form-control" value="{{ old('branch_type', $branch->branch_type ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $branch->phone ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Email</label><input name="email" class="form-control" value="{{ old('email', $branch->email ?? '') }}"></div>
    <div class="col-md-8 mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control">{{ old('address', $branch->address ?? '') }}</textarea></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $branch->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $branch->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
