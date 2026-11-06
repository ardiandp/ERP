<?php

namespace App\Http\Controllers;

use App\Models\AssetMovement;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\OvertimeRecord;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Services\AssetService;
use App\Services\AttendanceService;
use App\Services\ExpenseService;
use App\Services\PayrollService;
use App\Services\PurchaseOrderService;
use App\Services\StockOpnameService;
use App\Services\StockTransferService;
use Exception;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $poService,
        protected ExpenseService $expenseService,
        protected StockTransferService $transferService,
        protected StockOpnameService $opnameService,
        protected PayrollService $payrollService,
        protected AttendanceService $attendanceService,
        protected AssetService $assetService
    ) {}

    public function index(Request $request)
    {
        $branchId = $request->integer('branch_id') ?: null;

        $purchaseOrders = PurchaseOrder::with(['branch', 'supplier', 'creator'])
            ->where('status', 'DRAFT')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->get();

        $expenses = Expense::with(['branch', 'category', 'supplier', 'creator'])
            ->where('status', 'DRAFT')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->get();

        $stockTransfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'requester', 'items.product'])
            ->where('status', 'DRAFT')
            ->latest('id')
            ->get();

        $stockOpnames = StockOpname::with(['warehouse', 'creator', 'items.product'])
            ->where('status', 'DRAFT')
            ->latest('id')
            ->get();

        $payrolls = Payroll::with(['employee.branch', 'period'])
            ->where('status', 'DRAFT')
            ->when($branchId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('branch_id', $branchId)))
            ->latest('id')
            ->get();

        $overtimes = OvertimeRecord::with('employee.branch')
            ->where('status', 'PENDING')
            ->when($branchId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('branch_id', $branchId)))
            ->latest('id')
            ->get();

        $assetMovements = AssetMovement::with(['asset', 'fromBranch', 'toBranch', 'movedBy'])
            ->whereNull('approved_by')
            ->latest('id')
            ->get();

        $branches = Branch::orderBy('name')->get();

        return view('approvals.index', compact(
            'purchaseOrders',
            'expenses',
            'stockTransfers',
            'stockOpnames',
            'payrolls',
            'overtimes',
            'assetMovements',
            'branches',
            'branchId'
        ));
    }

    public function approve(Request $request, string $type, int $id)
    {
        try {
            switch ($type) {
                case 'purchase_order':
                    $item = PurchaseOrder::findOrFail($id);
                    $this->poService->approve($item);
                    break;

                case 'expense':
                    $item = Expense::findOrFail($id);
                    $this->expenseService->approve($item);
                    break;

                case 'stock_transfer':
                    $item = StockTransfer::findOrFail($id);
                    $this->transferService->approve($item);
                    break;

                case 'stock_opname':
                    $item = StockOpname::findOrFail($id);
                    $this->opnameService->approve($item);
                    break;

                case 'payroll':
                    $item = Payroll::findOrFail($id);
                    $this->payrollService->approve($item);
                    break;

                case 'overtime':
                    $item = OvertimeRecord::findOrFail($id);
                    $this->attendanceService->approveOvertime($item);
                    break;

                case 'asset_movement':
                    $item = AssetMovement::findOrFail($id);
                    $this->assetService->approveMovement($item);
                    break;

                default:
                    return back()->with('error', 'Tipe persetujuan tidak valid.');
            }

            AuditLog::record('approve', $item, [], [
                'status' => $item->status ?? ($item->approved_by ? 'APPROVED' : null),
                'approved_by' => $item->approved_by ?? auth()->id(),
            ]);

            return back()->with('success', 'Pengajuan berhasil disetujui.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyetujui: '.$e->getMessage());
        }
    }

    public function reject(Request $request, string $type, int $id)
    {
        try {
            switch ($type) {
                case 'purchase_order':
                    $item = PurchaseOrder::findOrFail($id);
                    $item->update(['status' => 'CANCELLED']);
                    break;

                case 'expense':
                    $item = Expense::findOrFail($id);
                    $item->update(['status' => 'REJECTED']);
                    break;

                case 'stock_transfer':
                    $item = StockTransfer::findOrFail($id);
                    $item->update(['status' => 'CANCELLED']);
                    break;

                case 'stock_opname':
                    $item = StockOpname::findOrFail($id);
                    $item->update(['status' => 'CANCELLED']);
                    break;

                case 'payroll':
                    $item = Payroll::findOrFail($id);
                    $item->update(['status' => 'CANCELLED']);
                    break;

                case 'overtime':
                    $item = OvertimeRecord::findOrFail($id);
                    $item->update(['status' => 'REJECTED']);
                    break;

                case 'asset_movement':
                    $item = AssetMovement::findOrFail($id);
                    $item->delete();
                    break;

                default:
                    return back()->with('error', 'Tipe penolakan tidak valid.');
            }

            AuditLog::record('reject', $item, [], [
                'status' => $item->status ?? 'REJECTED',
            ]);

            return back()->with('success', 'Pengajuan berhasil ditolak.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menolak: '.$e->getMessage());
        }
    }
}

