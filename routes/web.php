<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MasterCrudController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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
});
