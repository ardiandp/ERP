<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::latest()->paginate(15);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $company = Company::create($data);
        AuditLog::record('create', $company, [], $data);

        return redirect()->route('companies.index')->with('success', 'Company berhasil dibuat.');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $old = $company->getOriginal();
        $data = $this->validateData($request, $company);
        $company->update($data);
        AuditLog::record('update', $company, $old, $data);

        return redirect()->route('companies.index')->with('success', 'Company berhasil diperbarui.');
    }

    public function destroy(Company $company)
    {
        AuditLog::record('delete', $company, $company->toArray());
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company berhasil dihapus.');
    }

    protected function validateData(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:companies,code,'.($company?->id)],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
