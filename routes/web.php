<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillTemplateController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DigitalAccountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterCrudController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('companies', CompanyController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('positions', PositionController::class)->except('show');
    Route::resource('employees', EmployeeController::class);
    Route::resource('customers', CustomerController::class)->except('show');

    Route::prefix('master')->name('masterdata.')->group(function () {
        Route::get('{entity}', [MasterCrudController::class, 'index'])->name('index');
        Route::get('{entity}/create', [MasterCrudController::class, 'create'])->name('create');
        Route::post('{entity}', [MasterCrudController::class, 'store'])->name('store');
        Route::get('{entity}/{id}/edit', [MasterCrudController::class, 'edit'])->name('edit');
        Route::put('{entity}/{id}', [MasterCrudController::class, 'update'])->name('update');
        Route::delete('{entity}/{id}', [MasterCrudController::class, 'destroy'])->name('destroy');
    });

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');

    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::get('inventory/stock', [InventoryController::class, 'stock'])->name('inventory.stock');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('inventory/adjustment', [InventoryController::class, 'adjustmentForm'])->name('inventory.adjustment.create');
    Route::post('inventory/adjustment', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustment.store');

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::patch('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::delete('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');

    Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    Route::get('goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
    Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    Route::get('goods-receipts/{goods_receipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');

    Route::get('stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
    Route::get('stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create');
    Route::post('stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store');
    Route::get('stock-transfers/{stock_transfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');
    Route::patch('stock-transfers/{stock_transfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve');

    Route::get('stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
    Route::get('stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create');
    Route::post('stock-opnames', [StockOpnameController::class, 'store'])->name('stock-opnames.store');
    Route::get('stock-opnames/{stock_opname}', [StockOpnameController::class, 'show'])->name('stock-opnames.show');
    Route::patch('stock-opnames/{stock_opname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve');

    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('sales', [SalesController::class, 'store'])->name('sales.store');
    Route::post('sales/{sale}/payments', [PaymentController::class, 'store'])->name('sales.payments.store');
    Route::get('sales/{sale}', [SalesController::class, 'show'])->name('sales.show');

    Route::get('returns', [SalesReturnController::class, 'index'])->name('sales.returns.index');
    Route::get('sales/{sale}/returns/create', [SalesReturnController::class, 'create'])->name('sales.returns.create');
    Route::post('sales/{sale}/returns', [SalesReturnController::class, 'store'])->name('sales.returns.store');

    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');

    Route::resource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
    Route::post('bookings/{booking}/items', [BookingController::class, 'storeItem'])->name('bookings.items.store');
    Route::delete('bookings/{booking}/items/{item}', [BookingController::class, 'destroyItem'])->name('bookings.items.destroy');

    Route::get('finance/accounts', [FinanceController::class, 'accounts'])->name('finance.accounts');

    Route::get('bank-transactions', [BankTransactionController::class, 'index'])->name('bank-transactions.index');
    Route::get('bank-transactions/create', [BankTransactionController::class, 'create'])->name('bank-transactions.create');
    Route::post('bank-transactions', [BankTransactionController::class, 'store'])->name('bank-transactions.store');

    Route::get('bank-reconciliations', [BankReconciliationController::class, 'index'])->name('bank-reconciliations.index');
    Route::get('bank-reconciliations/create', [BankReconciliationController::class, 'create'])->name('bank-reconciliations.create');
    Route::post('bank-reconciliations', [BankReconciliationController::class, 'store'])->name('bank-reconciliations.store');

    Route::resource('bill-templates', BillTemplateController::class)->except('show');

    Route::get('bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('bills/generate', [BillController::class, 'generate'])->name('bills.generate');
    Route::post('bills/generate', [BillController::class, 'storeGenerate'])->name('bills.store-generate');
    Route::get('bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::patch('bills/{bill}/pay', [BillController::class, 'pay'])->name('bills.pay');

    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::patch('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');

    Route::resource('shifts', ShiftController::class)->except('show');
    Route::post('shifts/assignments', [ShiftController::class, 'assign'])->name('shifts.assign');
    Route::delete('shifts/assignments/{employee_shift}', [ShiftController::class, 'unassign'])->name('shifts.unassign');

    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    Route::get('overtimes', [OvertimeController::class, 'index'])->name('overtimes.index');
    Route::get('overtimes/create', [OvertimeController::class, 'create'])->name('overtimes.create');
    Route::post('overtimes', [OvertimeController::class, 'store'])->name('overtimes.store');
    Route::patch('overtimes/{overtime}/approve', [OvertimeController::class, 'approve'])->name('overtimes.approve');

    Route::get('salary-structures', [SalaryStructureController::class, 'index'])->name('salary-structures.index');
    Route::get('salary-structures/create', [SalaryStructureController::class, 'create'])->name('salary-structures.create');
    Route::post('salary-structures', [SalaryStructureController::class, 'store'])->name('salary-structures.store');

    Route::get('payroll-periods', [PayrollPeriodController::class, 'index'])->name('payroll-periods.index');
    Route::get('payroll-periods/create', [PayrollPeriodController::class, 'create'])->name('payroll-periods.create');
    Route::post('payroll-periods', [PayrollPeriodController::class, 'store'])->name('payroll-periods.store');
    Route::get('payroll-periods/{payroll_period}', [PayrollPeriodController::class, 'show'])->name('payroll-periods.show');
    Route::patch('payroll-periods/{payroll_period}/close', [PayrollPeriodController::class, 'close'])->name('payroll-periods.close');
    Route::post('payroll-periods/{payroll_period}/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');

    Route::get('payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
    Route::get('payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
    Route::post('payrolls/{payroll}/adjust', [PayrollController::class, 'adjust'])->name('payrolls.adjust');
    Route::patch('payrolls/{payroll}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::post('payrolls/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payrolls.payslip');
    Route::post('payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');

    Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');
    Route::get('payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show');
    Route::patch('payslips/{payslip}/publish', [PayslipController::class, 'publish'])->name('payslips.publish');

    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/movements', [AssetController::class, 'move'])->name('assets.movements.store');
    Route::patch('asset-movements/{asset_movement}/approve', [AssetController::class, 'approveMovement'])->name('asset-movements.approve');

    Route::get('media', [MediaFileController::class, 'index'])->name('media.index');
    Route::get('media/create', [MediaFileController::class, 'create'])->name('media.create');
    Route::post('media', [MediaFileController::class, 'store'])->name('media.store');
    Route::get('media/{media}', [MediaFileController::class, 'show'])->name('media.show');
    Route::delete('media/{media}', [MediaFileController::class, 'destroy'])->name('media.destroy');

    Route::resource('digital-accounts', DigitalAccountController::class)->except('show');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
});
