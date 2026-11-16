<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InspectionController as AdminInspectionController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\JobOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Billing\AccountController as BillingAccountController;
use App\Http\Controllers\Billing\DashboardController as BillingDashboardController;
use App\Http\Controllers\Billing\PaymentController as BillingPaymentController;
use App\Http\Controllers\Billing\ReportController as BillingReportController;
use App\Http\Controllers\Custodian\DashboardController as CustodianDashboardController;
use App\Http\Controllers\Custodian\InventoryController as CustodianInventoryController;
use App\Http\Controllers\Field\DashboardController as FieldDashboardController;
use App\Http\Controllers\Field\InspectionController as FieldInspectionController;
use App\Http\Controllers\Field\JobOrderController as FieldJobOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StyleguideController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');

Route::get('/styleguide', [StyleguideController::class, 'index'])->name('styleguide');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin — client accounts and contracts
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:Admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'archive'])->name('clients.archive');
        Route::put('/clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');

        Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
        Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
        Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
        Route::put('/contracts/{contract}/archive', [ContractController::class, 'archive'])->name('contracts.archive');
        Route::put('/contracts/{contract}/restore', [ContractController::class, 'restore'])->name('contracts.restore');
    });
});

/*
|--------------------------------------------------------------------------
| Billing — billing records and read-only client accounts
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:Admin,Billing Officer'])->group(function () {
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/dashboard', BillingDashboardController::class)->name('dashboard');
        Route::get('/payments', [BillingPaymentController::class, 'index'])->name('payments');
        Route::post('/payments', [BillingPaymentController::class, 'store'])->name('payments.store');
        Route::get('/reports', BillingReportController::class)->name('reports');
        Route::get('/accounts', [BillingAccountController::class, 'index'])->name('accounts');
        Route::get('/accounts/{client}', [BillingAccountController::class, 'show'])->name('accounts.show');
    });
});

/*
|--------------------------------------------------------------------------
| Admin only — platform administration
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:Admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{inventory_item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{inventory_item}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/{inventory_item}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        Route::get('/job-orders', [JobOrderController::class, 'index'])->name('job-orders');
        Route::get('/job-orders/create', [JobOrderController::class, 'create'])->name('job-orders.create');
        Route::post('/job-orders', [JobOrderController::class, 'store'])->name('job-orders.store');
        Route::get('/job-orders/{job_order}/edit', [JobOrderController::class, 'edit'])->name('job-orders.edit');
        Route::put('/job-orders/{job_order}', [JobOrderController::class, 'update'])->name('job-orders.update');

        Route::get('/inspections', [AdminInspectionController::class, 'index'])->name('inspections');
        Route::get('/inspections/create', [AdminInspectionController::class, 'create'])->name('inspections.create');
        Route::post('/inspections', [AdminInspectionController::class, 'store'])->name('inspections.store');
        Route::get('/inspections/{inspection}', [AdminInspectionController::class, 'show'])->name('inspections.show');
        Route::get('/inspections/{inspection}/edit', [AdminInspectionController::class, 'edit'])->name('inspections.edit');
        Route::put('/inspections/{inspection}', [AdminInspectionController::class, 'update'])->name('inspections.update');

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
        Route::post('/payments', [AdminPaymentController::class, 'store'])->name('payments.store');

        Route::get('/reports', AdminReportController::class)->name('reports');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    });
});

/*
|--------------------------------------------------------------------------
| Field Technician (Admin may also view for oversight)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:Admin,Field Technician'])->group(function () {
    Route::prefix('field')->name('field.')->group(function () {
        Route::get('/dashboard', FieldDashboardController::class)->name('dashboard');
        Route::get('/job-orders', [FieldJobOrderController::class, 'index'])->name('job-orders');
        Route::get('/job-orders/{job_order}', [FieldJobOrderController::class, 'show'])->name('job-orders.show');
        Route::post('/job-orders/{job_order}/status', [FieldJobOrderController::class, 'updateStatus'])->name('job-orders.status');
        Route::post('/job-orders/{job_order}/materials', [FieldJobOrderController::class, 'logMaterials'])->name('job-orders.materials');

        Route::get('/inspections', [FieldInspectionController::class, 'index'])->name('inspections');
        Route::get('/inspections/{inspection}', [FieldInspectionController::class, 'show'])->name('inspections.show');
        Route::put('/inspections/{inspection}', [FieldInspectionController::class, 'update'])->name('inspections.update');
    });
});

/*
|--------------------------------------------------------------------------
| Chemical Custodian (Admin may also view for oversight)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:Admin,Chemical Custodian'])->group(function () {
    Route::prefix('custodian')->name('custodian.')->group(function () {
        Route::get('/dashboard', CustodianDashboardController::class)->name('dashboard');
        Route::get('/inventory', [CustodianInventoryController::class, 'index'])->name('inventory');
        Route::post('/inventory/{inventory_item}/adjust', [CustodianInventoryController::class, 'adjust'])->name('inventory.adjust');
    });
});
