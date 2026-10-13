<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('company')->latest()->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $companies = Company::all();

        return view('branches.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $branch = Branch::create($data);
        AuditLog::record('create', $branch, [], $data);

        return redirect()->route('branches.index')->with('success', 'Branch berhasil dibuat.');
    }

    public function show(Branch $branch)
    {
        return view('branches.show', ['branch' => $branch->load('company')]);
    }

    public function edit(Branch $branch)
    {
        $companies = Company::all();

        return view('branches.edit', compact('branch', 'companies'));
    }

    public function update(Request $request, Branch $branch)
    {
        $old = $branch->getOriginal();
        $data = $this->validateData($request, $branch);
        $branch->update($data);
        AuditLog::record('update', $branch, $old, $data);

        return redirect()->route('branches.index')->with('success', 'Branch berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        AuditLog::record('delete', $branch, $branch->toArray());
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Branch berhasil dihapus.');
    }

    protected function validateData(Request $request, ?Branch $branch = null): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code,'.($branch?->id)],
            'name' => ['required', 'string', 'max:255'],
            'branch_type' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
