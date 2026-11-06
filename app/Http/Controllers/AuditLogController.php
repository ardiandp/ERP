<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('auditable_type')) {
            $type = $request->input('auditable_type');
            $query->where(function ($q) use ($type) {
                $q->where('auditable_type', $type)
                    ->orWhere('auditable_type', 'like', '%\\'.$type);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $logs = $query->latest('id')->limit(500)->get();

        $actions = AuditLog::query()->select('action')->distinct()->pluck('action');
        $rawTypes = AuditLog::query()->whereNotNull('auditable_type')->select('auditable_type')->distinct()->pluck('auditable_type');
        $entityTypes = $rawTypes->map(fn ($full) => class_basename($full))->unique()->sort()->values();
        $users = User::orderBy('name')->get();

        return view('audit-logs.index', compact('logs', 'actions', 'entityTypes', 'users'));
    }

    public function show(AuditLog $audit_log)
    {
        $audit_log->load('user');

        return response()->json([
            'id' => $audit_log->id,
            'action' => $audit_log->action,
            'auditable_type' => class_basename($audit_log->auditable_type),
            'auditable_id' => $audit_log->auditable_id,
            'user' => $audit_log->user?->name ?? 'System',
            'ip_address' => $audit_log->ip_address,
            'user_agent' => $audit_log->user_agent,
            'created_at' => optional($audit_log->created_at)->format('d/m/Y H:i:s'),
            'old_values' => $audit_log->old_values,
            'new_values' => $audit_log->new_values,
        ]);
    }
}

