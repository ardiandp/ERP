<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterCrudController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;
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
});
