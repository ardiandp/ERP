@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Employee Code</label><input name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Full Name</label><input name="full_name" class="form-control" value="{{ old('full_name', $employee->full_name ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">NIK</label><input name="nik" class="form-control" value="{{ old('nik', $employee->nik ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Birth Place</label><input name="birth_place" class="form-control" value="{{ old('birth_place', $employee->birth_place ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Birth Date</label><input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($employee->birth_date ?? null)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">-</option>
            <option value="M" @selected(old('gender', $employee->gender ?? '') === 'M')>Male</option>
            <option value="F" @selected(old('gender', $employee->gender ?? '') === 'F')>Female</option>
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $employee->phone ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Email</label><input name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Marital Status</label><input name="marital_status" class="form-control" value="{{ old('marital_status', $employee->marital_status ?? '') }}"></div>
    <div class="col-md-8 mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ old('address', $employee->address ?? '') }}</textarea></div>
    <div class="col-md-4 mb-3"><label class="form-label">Employment Status</label><input name="employment_status" class="form-control" value="{{ old('employment_status', $employee->employment_status ?? 'ACTIVE') }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Join Date</label><input type="date" name="join_date" class="form-control" value="{{ old('join_date', optional($employee->join_date ?? null)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Resign Date</label><input type="date" name="resign_date" class="form-control" value="{{ old('resign_date', optional($employee->resign_date ?? null)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Branch</label>
        <select name="branch_id" class="form-select">
            @foreach($branches as $b)
            <option value="{{ $b->id }}" @selected(old('branch_id', $employee->branch_id ?? '') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Department</label>
        <select name="department_id" class="form-select">
            <option value="">-</option>
            @foreach($departments as $d)
            <option value="{{ $d->id }}" @selected(old('department_id', $employee->department_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Position</label>
        <select name="position_id" class="form-select">
            <option value="">-</option>
            @foreach($positions as $p)
            <option value="{{ $p->id }}" @selected(old('position_id', $employee->position_id ?? '') == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Manager</label>
        <select name="manager_id" class="form-select">
            <option value="">-</option>
            @foreach($managers as $m)
            <option value="{{ $m->id }}" @selected(old('manager_id', $employee->manager_id ?? '') == $m->id)>{{ $m->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $employee->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="INACTIVE" @selected(old('status', $employee->status ?? '') === 'INACTIVE')>INACTIVE</option>
        </select>
    </div>
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
