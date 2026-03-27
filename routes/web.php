<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;
use App\Http\Controllers\Billing\BillingDraftController;
use App\Http\Controllers\Billing\ImportsMonthlySetupController;
use App\Http\Controllers\Billing\MasterDataDraftController;
use App\Http\Controllers\Admin\AdminUsersController;

Route::get('/', function () {
    return session()->has('user_id')
        ? redirect('/ui/dashboard')
        : redirect('/login');
});

Route::get('/login', [AuthDraftController::class, 'showLogin']);
Route::post('/login', [AuthDraftController::class, 'login']);
Route::get('/logout', [AuthDraftController::class, 'logout']);

Route::get('/forgot-password', [AuthDraftController::class, 'showForgotPassword']);
Route::post('/forgot-password', [AuthDraftController::class, 'forgotPassword']);

Route::get('/reset-password', [AuthDraftController::class, 'showResetPassword']);
Route::post('/reset-password', [AuthDraftController::class, 'resetPassword']);

Route::middleware(['ensure.auth', 'force.password.change', 'shell.rbac'])->group(function () {
    Route::get('/ui/profile', [AuthDraftController::class, 'showProfile']);
    Route::post('/api/profile/change-password', [AuthDraftController::class, 'changePassword']);

    Route::view('/ui/dashboard', 'auth.dashboard');
    Route::view('/ui/reports', 'auth.reports');
    Route::view('/ui/reconciliation', 'auth.reconciliation');
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN'])->group(function () {
    Route::get('/ui/admin/users', [AdminUsersController::class, 'index']);
    Route::post('/api/admin/users/create', [AdminUsersController::class, 'create']);
    Route::post('/api/admin/users/update', [AdminUsersController::class, 'update']);
    Route::post('/api/admin/users/reset-password', [AdminUsersController::class, 'resetPassword']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::view('/ui/billing', 'auth.billing');
    Route::view('/ui/month-cycle', 'auth.month-cycle');
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN', 'month.guard.shell'])->group(function () {
    // Guard-shell routes only. No month domain logic implemented.
    Route::post('/month/open', fn () => response()->json(['status' => 'ok', 'route' => '/month/open', 'mode' => 'guard-shell-pass']));
    Route::post('/month/transition', fn () => response()->json(['status' => 'ok', 'route' => '/month/transition', 'mode' => 'guard-shell-exception-pass']));

    Route::post('/rates/upsert', [BillingDraftController::class, 'ratesUpsert']);
    Route::post('/rates/approve', [BillingDraftController::class, 'ratesApprove']);

    // Billing core endpoints currently in migration.
    Route::post('/billing/run', [BillingDraftController::class, 'run']);
    Route::post('/api/billing/precheck', [BillingDraftController::class, 'precheck']);
    Route::post('/api/billing/finalize', [BillingDraftController::class, 'finalize']);
    Route::post('/billing/elec/compute', [BillingDraftController::class, 'elecCompute']);
    Route::post('/billing/water/compute', [BillingDraftController::class, 'waterCompute']);
    Route::post('/billing/run', [BillingDraftController::class, 'run']);
    Route::post('/billing/fingerprint', [BillingDraftController::class, 'fingerprint']);
    Route::get('/billing/adjustments/list', [BillingDraftController::class, 'adjustmentsList']);
    Route::get('/billing/print/{month_cycle}/{employee_id}', [BillingDraftController::class, 'printEmployee']);
    Route::post('/billing/lock', [BillingDraftController::class, 'lock']);
    Route::post('/billing/approve', [BillingDraftController::class, 'approve']);

    // Adjustments / recovery (evidence shows removed/disabled flow -> explicit real 410 parity behavior).
    Route::post('/billing/adjustments/create', [BillingDraftController::class, 'adjustmentCreate']);
    Route::post('/billing/adjustments/approve', [BillingDraftController::class, 'adjustmentApprove']);
    Route::post('/recovery/payment', [BillingDraftController::class, 'recoveryPayment']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::get('/units', [MasterDataDraftController::class, 'units']);
    Route::get('/rooms', [MasterDataDraftController::class, 'rooms']);
    Route::get('/occupancy/context', [MasterDataDraftController::class, 'occupancyContext']);
    Route::get('/occupancy', [MasterDataDraftController::class, 'occupancy']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY'])->group(function () {
    Route::post('/units/upsert', [MasterDataDraftController::class, 'unitsUpsert']);
    Route::delete('/units/{unitId}', [MasterDataDraftController::class, 'unitsDelete']);

    Route::post('/rooms/upsert', [MasterDataDraftController::class, 'roomsUpsert']);
    Route::delete('/rooms/{id}', [MasterDataDraftController::class, 'roomsDelete']);

    Route::post('/occupancy/upsert', [MasterDataDraftController::class, 'occupancyUpsert']);
    Route::delete('/occupancy/{id}', [MasterDataDraftController::class, 'occupancyDelete']);
    Route::post('/api/occupancy/autofill', [MasterDataDraftController::class, 'occupancyAutofill']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,VIEWER'])->group(function () {
    Route::get('/reports/reconciliation', [BillingDraftController::class, 'reconciliationReport']);
    Route::get('/reports/monthly-summary', [BillingDraftController::class, 'monthlySummary']);
    Route::get('/reports/recovery', [BillingDraftController::class, 'recoveryReport']);
    Route::get('/reports/employee-bill-summary', [BillingDraftController::class, 'employeeBillSummary']);
    Route::get('/reports/van', [BillingDraftController::class, 'vanReport']);
    Route::get('/reports/elec-summary', [BillingDraftController::class, 'elecSummary']);
    Route::get('/export/excel/reconciliation', [BillingDraftController::class, 'exportExcelReconciliation']);
});

require __DIR__.'/electric_v1.php';
