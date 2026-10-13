@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $company->code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Nama</label><input name="name" class="form-control" value="{{ old('name', $company->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Legal Name</label><input name="legal_name" class="form-control" value="{{ old('legal_name', $company->legal_name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">NPWP</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $company->tax_number ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $company->phone ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Email</label><input name="email" class="form-control" value="{{ old('email', $company->email ?? '') }}"></div>
    <div class="col-md-8 mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control">{{ old('address', $company->address ?? '') }}</textarea></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $company->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $company->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
