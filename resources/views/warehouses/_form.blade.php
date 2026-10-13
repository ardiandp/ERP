@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            <option value="">-</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $warehouse->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $warehouse->code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $warehouse->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Type</label>
        <select name="warehouse_type" class="form-select">
            @foreach(['MAIN', 'STORE', 'TRANSIT'] as $type)
            <option value="{{ $type }}" @selected(old('warehouse_type', $warehouse->warehouse_type ?? 'MAIN') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $warehouse->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $warehouse->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
    <div class="col-md-12 mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ old('address', $warehouse->address ?? '') }}</textarea></div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
