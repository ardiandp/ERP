@csrf
<div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $template->code ?? '') }}"></div>
    <div class="col-md-5 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $template->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Category</label><input name="category" class="form-control" value="{{ old('category', $template->category ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Frequency</label>
        <select name="frequency" class="form-select">
            @foreach(['MONTHLY', 'QUARTERLY', 'SEMESTERLY', 'YEARLY', 'ONE_TIME'] as $frequency)
            <option value="{{ $frequency }}" @selected(old('frequency', $template->frequency ?? 'MONTHLY') === $frequency)>{{ $frequency }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Default Amount</label><input type="number" step="0.01" name="default_amount" class="form-control" value="{{ old('default_amount', $template->default_amount ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Default Due Day</label><input type="number" name="default_due_day" class="form-control" value="{{ old('default_due_day', $template->default_due_day ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            <option value="">-</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $template->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
            <option value="">-</option>
            @foreach($suppliers as $supplier)
            <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $template->supplier_id ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Financial Account</label>
        <select name="financial_account_id" class="form-select">
            <option value="">-</option>
            @foreach($accounts as $account)
            <option value="{{ $account->id }}" @selected((string) old('financial_account_id', $template->financial_account_id ?? '') === (string) $account->id)>{{ $account->account_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $template->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $template->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
    <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes', $template->notes ?? '') }}</textarea></div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
