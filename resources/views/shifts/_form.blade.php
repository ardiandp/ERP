@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            <option value="">-</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $shift->branch_id ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $shift->code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $shift->name ?? '') }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">Start Time</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $shift->start_time ?? '') }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $shift->end_time ?? '') }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">Break (minutes)</label><input type="number" name="break_minutes" class="form-control" value="{{ old('break_minutes', $shift->break_minutes ?? 0) }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $shift->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $shift->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
