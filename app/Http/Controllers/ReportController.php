<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $service) {}

    public function index(Request $request)
    {
        $types = $this->service->types();
        $type = $request->input('type', 'sales_daily');
        abort_unless(isset($types[$type]), 404);
        $filters = $this->filters($request);

        return view('reports.index', [
            'types' => $types,
            'type' => $type,
            'filters' => $filters,
            'branches' => Branch::orderBy('name')->get(),
            'report' => $this->service->build($type, $filters),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $types = $this->service->types();
        $type = $request->input('type', 'sales_daily');
        abort_unless(isset($types[$type]), 404);
        $report = $this->service->build($type, $this->filters($request));

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $report['columns']);
            foreach ($report['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $type.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    protected function filters(Request $request): array
    {
        return [
            'from' => $request->input('from', now()->startOfMonth()->toDateString()),
            'to' => $request->input('to', now()->toDateString()),
            'branch_id' => $request->integer('branch_id') ?: null,
        ];
    }
}
