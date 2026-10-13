@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="customer_code" class="form-control" value="{{ old('customer_code', $customer->customer_code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">-</option>
            <option value="M" @selected(old('gender', $customer->gender ?? '') === 'M')>Male</option>
            <option value="F" @selected(old('gender', $customer->gender ?? '') === 'F')>Female</option>
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Birth Date</label><input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($customer->birth_date ?? null)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Email</label><input name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Type</label><input name="customer_type" class="form-control" value="{{ old('customer_type', $customer->customer_type ?? 'REGULAR') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Source</label><input name="source" class="form-control" value="{{ old('source', $customer->source ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $customer->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $customer->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
    <div class="col-md-8 mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea></div>
    <div class="col-md-4 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea></div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
