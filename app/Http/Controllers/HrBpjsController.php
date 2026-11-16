<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeInsurance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrBpjsController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'ACTIVE');
        $branchId = $request->integer('branch_id') ?: null;
        $bpjsStatus = $request->input('bpjs_status', 'ALL');
        $employmentStatus = $request->input('employment_status');
        $search = trim((string) $request->input('search'));

        $query = Employee::with(['branch', 'department', 'position', 'insurances'])
            ->when($statusFilter !== 'ALL', fn ($q) => $q->where('status', $statusFilter))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($employmentStatus, fn ($q) => $q->where('employment_status', $employmentStatus));

        // Filter by BPJS status
        if ($bpjsStatus === 'BOTH') {
            $query->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE'))
                ->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'KES_ONLY') {
            $query->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE'))
                ->whereDoesntHave('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'TK_ONLY') {
            $query->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE'))
                ->whereDoesntHave('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'NONE') {
            $query->whereDoesntHave('insurances', fn ($q) => $q->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'HAS_ANY') {
            $query->whereHas('insurances', fn ($q) => $q->where('status', 'ACTIVE'));
        }

        // Search term
        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('employee_code', 'like', $term)
                    ->orWhere('nik', 'like', $term)
                    ->orWhereHas('insurances', fn ($ins) => $ins->where('membership_number', 'like', $term));
            });
        }

        $employees = $query->orderBy('full_name')->paginate(25)->withQueryString();

        // Calculate KPI metrics across all active employees
        $activeEmployeesQuery = Employee::where('status', 'ACTIVE');
        $totalActive = (clone $activeEmployeesQuery)->count();

        $kesCount = (clone $activeEmployeesQuery)->whereHas('insurances', function ($q) {
            $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE');
        })->count();

        $tkCount = (clone $activeEmployeesQuery)->whereHas('insurances', function ($q) {
            $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE');
        })->count();

        $bothCount = (clone $activeEmployeesQuery)->whereHas('insurances', function ($q) {
            $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE');
        })->whereHas('insurances', function ($q) {
            $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE');
        })->count();

        $unregisteredCount = (clone $activeEmployeesQuery)->whereDoesntHave('insurances', function ($q) {
            $q->where('status', 'ACTIVE');
        })->count();

        $totalEmpContribution = (float) EmployeeInsurance::where('status', 'ACTIVE')
            ->whereHas('employee', fn ($q) => $q->where('status', 'ACTIVE'))
            ->sum('employee_contribution');

        $totalCompContribution = (float) EmployeeInsurance::where('status', 'ACTIVE')
            ->whereHas('employee', fn ($q) => $q->where('status', 'ACTIVE'))
            ->sum('company_contribution');

        return view('hr.bpjs.index', [
            'employees' => $employees,
            'branches' => Branch::orderBy('name')->get(),
            'metrics' => [
                'total_active' => $totalActive,
                'kes_count' => $kesCount,
                'tk_count' => $tkCount,
                'both_count' => $bothCount,
                'unregistered_count' => $unregisteredCount,
                'total_emp_contribution' => $totalEmpContribution,
                'total_comp_contribution' => $totalCompContribution,
                'total_monthly_bpjs' => $totalEmpContribution + $totalCompContribution,
            ],
            'filters' => [
                'status' => $statusFilter,
                'branch_id' => $branchId,
                'bpjs_status' => $bpjsStatus,
                'employment_status' => $employmentStatus,
                'search' => $search,
            ],
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            // BPJS Kesehatan
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'bpjs_kes_class' => ['nullable', 'string', 'in:1,2,3,I,II,III'],
            'bpjs_kes_start_date' => ['nullable', 'date'],
            'bpjs_kes_emp_contrib' => ['nullable', 'numeric', 'min:0'],
            'bpjs_kes_comp_contrib' => ['nullable', 'numeric', 'min:0'],
            'bpjs_kes_status' => ['nullable', 'in:ACTIVE,INACTIVE'],

            // BPJS Ketenagakerjaan
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bpjs_tk_start_date' => ['nullable', 'date'],
            'bpjs_tk_emp_contrib' => ['nullable', 'numeric', 'min:0'],
            'bpjs_tk_comp_contrib' => ['nullable', 'numeric', 'min:0'],
            'bpjs_tk_status' => ['nullable', 'in:ACTIVE,INACTIVE'],
        ]);

        $before = $employee->insurances()->get()->toArray();

        // 1. Process BPJS Kesehatan
        $kesNumber = trim((string) ($validated['bpjs_kes_number'] ?? ''));
        if ($kesNumber !== '') {
            EmployeeInsurance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'insurance_type' => 'BPJS_KES',
                ],
                [
                    'provider' => 'BPJS Kesehatan',
                    'membership_number' => $kesNumber,
                    'insurance_class' => $validated['bpjs_kes_class'] ?: '1',
                    'start_date' => $validated['bpjs_kes_start_date'] ?: now()->startOfMonth()->toDateString(),
                    'employee_contribution' => (float) ($validated['bpjs_kes_emp_contrib'] ?? 0),
                    'company_contribution' => (float) ($validated['bpjs_kes_comp_contrib'] ?? 0),
                    'status' => $validated['bpjs_kes_status'] ?? 'ACTIVE',
                ]
            );
        } else {
            // If empty, mark inactive if exists
            $existingKes = $employee->insurances()->where('insurance_type', 'BPJS_KES')->first();
            if ($existingKes) {
                $existingKes->update(['status' => 'INACTIVE']);
            }
        }

        // 2. Process BPJS Ketenagakerjaan
        $tkNumber = trim((string) ($validated['bpjs_tk_number'] ?? ''));
        if ($tkNumber !== '') {
            EmployeeInsurance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'insurance_type' => 'BPJS_TK',
                ],
                [
                    'provider' => 'BPJS Ketenagakerjaan',
                    'membership_number' => $tkNumber,
                    'insurance_class' => null,
                    'start_date' => $validated['bpjs_tk_start_date'] ?: now()->startOfMonth()->toDateString(),
                    'employee_contribution' => (float) ($validated['bpjs_tk_emp_contrib'] ?? 0),
                    'company_contribution' => (float) ($validated['bpjs_tk_comp_contrib'] ?? 0),
                    'status' => $validated['bpjs_tk_status'] ?? 'ACTIVE',
                ]
            );
        } else {
            // If empty, mark inactive if exists
            $existingTk = $employee->insurances()->where('insurance_type', 'BPJS_TK')->first();
            if ($existingTk) {
                $existingTk->update(['status' => 'INACTIVE']);
            }
        }

        $after = $employee->fresh()->insurances()->get()->toArray();
        AuditLog::record('update_bpjs', $employee, $before, $after);

        return redirect()->back()->with('success', "Data BPJS untuk {$employee->full_name} berhasil diperbarui.");
    }

    public function export(Request $request): StreamedResponse
    {
        $statusFilter = $request->input('status', 'ACTIVE');
        $branchId = $request->integer('branch_id') ?: null;
        $bpjsStatus = $request->input('bpjs_status', 'ALL');
        $employmentStatus = $request->input('employment_status');
        $search = trim((string) $request->input('search'));

        $query = Employee::with(['branch', 'department', 'position', 'insurances'])
            ->when($statusFilter !== 'ALL', fn ($q) => $q->where('status', $statusFilter))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($employmentStatus, fn ($q) => $q->where('employment_status', $employmentStatus));

        if ($bpjsStatus === 'BOTH') {
            $query->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE'))
                ->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'KES_ONLY') {
            $query->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE'))
                ->whereDoesntHave('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'TK_ONLY') {
            $query->whereHas('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_TK')->where('status', 'ACTIVE'))
                ->whereDoesntHave('insurances', fn ($q) => $q->where('insurance_type', 'BPJS_KES')->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'NONE') {
            $query->whereDoesntHave('insurances', fn ($q) => $q->where('status', 'ACTIVE'));
        } elseif ($bpjsStatus === 'HAS_ANY') {
            $query->whereHas('insurances', fn ($q) => $q->where('status', 'ACTIVE'));
        }

        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('employee_code', 'like', $term)
                    ->orWhere('nik', 'like', $term)
                    ->orWhereHas('insurances', fn ($ins) => $ins->where('membership_number', 'like', $term));
            });
        }

        $rows = $query->orderBy('full_name')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Kode Karyawan',
                'Nama Lengkap',
                'NIK',
                'Cabang',
                'Departemen',
                'Jabatan',
                'Status Karyawan',
                'BPJS Kesehatan (No. Peserta)',
                'Kelas BPJS Kes',
                'Iuran Kes - Karyawan (Rp)',
                'Iuran Kes - Perusahaan (Rp)',
                'Status BPJS Kes',
                'BPJS Ketenagakerjaan (No. KPJ)',
                'Iuran TK - Karyawan (Rp)',
                'Iuran TK - Perusahaan (Rp)',
                'Status BPJS TK',
                'Total Iuran Karyawan (Rp)',
                'Total Iuran Perusahaan (Rp)',
                'Total Iuran Bulanan (Rp)',
            ]);

            foreach ($rows as $emp) {
                $kes = $emp->bpjs_kes;
                $tk = $emp->bpjs_tk;

                $kesEmpContrib = (float) ($kes && $kes->status === 'ACTIVE' ? $kes->employee_contribution : 0);
                $kesCompContrib = (float) ($kes && $kes->status === 'ACTIVE' ? $kes->company_contribution : 0);
                $tkEmpContrib = (float) ($tk && $tk->status === 'ACTIVE' ? $tk->employee_contribution : 0);
                $tkCompContrib = (float) ($tk && $tk->status === 'ACTIVE' ? $tk->company_contribution : 0);

                $totalEmp = $kesEmpContrib + $tkEmpContrib;
                $totalComp = $kesCompContrib + $tkCompContrib;

                fputcsv($out, [
                    $emp->employee_code,
                    $emp->full_name,
                    $emp->nik ? "'".$emp->nik : '-',
                    $emp->branch?->name ?? '-',
                    $emp->department?->name ?? '-',
                    $emp->position?->name ?? '-',
                    $emp->employment_status,
                    $kes && $kes->status === 'ACTIVE' ? "'".$kes->membership_number : '-',
                    $kes && $kes->status === 'ACTIVE' ? 'Kelas '.($kes->insurance_class ?: '1') : '-',
                    $kesEmpContrib,
                    $kesCompContrib,
                    $kes ? $kes->status : 'BELUM TERDAFTAR',
                    $tk && $tk->status === 'ACTIVE' ? "'".$tk->membership_number : '-',
                    $tkEmpContrib,
                    $tkCompContrib,
                    $tk ? $tk->status : 'BELUM TERDAFTAR',
                    $totalEmp,
                    $totalComp,
                    $totalEmp + $totalComp,
                ]);
            }
            fclose($out);
        }, 'rekap-bpjs-karyawan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
