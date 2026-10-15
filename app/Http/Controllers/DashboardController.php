<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $service)
    {
        $branchId = $request->integer('branch_id') ?: null;

        return view('dashboard', [
            'branches' => Branch::orderBy('name')->get(),
            'selectedBranch' => $branchId,
            'metrics' => $service->owner($branchId),
            'branchMetrics' => $branchId ? $service->branch($branchId) : null,
        ]);
    }
}
