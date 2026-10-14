@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <option value="">-</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $account->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $account->name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Username</label><input name="username" class="form-control" value="{{ old('username', $account->username ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Email</label><input name="email" class="form-control" value="{{ old('email', $account->email ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">URL</label><input name="url" class="form-control" value="{{ old('url', $account->url ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Renewal Date</label><input type="date" name="renewal_date" class="form-control" value="{{ old('renewal_date', optional($account->renewal_date ?? null)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['ACTIVE', 'INACTIVE', 'EXPIRED'] as $status)
            <option value="{{ $status }}" @selected(old('status', $account->status ?? 'ACTIVE') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8 mb-3"><label class="form-label">Notes</label><input name="notes" class="form-control" value="{{ old('notes', $account->notes ?? '') }}"></div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
