<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use App\Models\EmployeeSalaryStructure;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryStructureController extends Controller
{
    public function index()
    {
        return view('salary-structures.index', [
            'employees' => Employee::with(['salaryStructures' => fn ($query) => $query->orderByDesc('effective_date'), 'salaryComponents.salaryComponent'])
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('salary-structures.create', [
            'employees' => Employee::orderBy('full_name')->get(),
            'components' => SalaryComponent::where('status', 'ACTIVE')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'effective_date' => ['required', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'components' => ['nullable', 'array'],
            'components.*.salary_component_id' => ['nullable', 'exists:salary_components,id'],
            'components.*.amount' => ['nullable', 'numeric', 'min:0'],
            'components.*.calculation_type' => ['nullable', 'in:FIXED,PERCENTAGE'],
        ]);

        DB::transaction(function () use ($data) {
            EmployeeSalaryStructure::create([
                'employee_id' => $data['employee_id'],
                'effective_date' => $data['effective_date'],
                'basic_salary' => $data['basic_salary'],
                'notes' => $data['notes'] ?? null,
                'status' => 'ACTIVE',
            ]);

            foreach ($data['components'] ?? [] as $component) {
                if (empty($component['salary_component_id'])) {
                    continue;
                }

                EmployeeSalaryComponent::create([
                    'employee_id' => $data['employee_id'],
                    'salary_component_id' => $component['salary_component_id'],
                    'amount' => $component['amount'] ?? 0,
                    'calculation_type' => $component['calculation_type'] ?? 'FIXED',
                    'effective_date' => $data['effective_date'],
                    'status' => 'ACTIVE',
                ]);
            }
        });

        return redirect()->route('salary-structures.index')->with('success', 'Struktur gaji disimpan.');
    }
}
