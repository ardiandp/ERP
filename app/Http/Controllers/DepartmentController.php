<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('departments.index', ['departments' => Department::with('company')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('departments.create', ['companies' => Company::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['required', 'unique:departments,code'],
            'name' => ['required', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $department = Department::create($data);
        AuditLog::record('create', $department, [], $data);

        return redirect()->route('departments.index')->with('success', 'Department dibuat.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', ['department' => $department, 'companies' => Company::all()]);
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['required', 'unique:departments,code,'.$department->id],
            'name' => ['required', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $department->update($data);
        AuditLog::record('update', $department, [], $data);

        return redirect()->route('departments.index')->with('success', 'Department diperbarui.');
    }

    public function destroy(Department $department)
    {
        AuditLog::record('delete', $department, $department->toArray());
        $department->delete();

        return back()->with('success', 'Department dihapus.');
    }
}
