@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Asset Code</label><input name="asset_code" class="form-control" value="{{ old('asset_code', $asset->asset_code ?? '') }}" placeholder="otomatis jika kosong"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Category</label>
        <select name="asset_category_id" class="form-select">
            <option value="">-</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('asset_category_id', $asset->asset_category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $asset->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', optional($asset->purchase_date ?? null)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $asset->purchase_price ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            <option value="">-</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $asset->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location', $asset->location ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Condition</label>
        <select name="condition" class="form-select">
            @foreach(['GOOD', 'FAIR', 'DAMAGED'] as $condition)
            <option value="{{ $condition }}" @selected(old('condition', $asset->condition ?? 'GOOD') === $condition)>{{ $condition }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['ACTIVE', 'INACTIVE', 'DISPOSED'] as $status)
            <option value="{{ $status }}" @selected(old('status', $asset->status ?? 'ACTIVE') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes', $asset->notes ?? '') }}</textarea></div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
