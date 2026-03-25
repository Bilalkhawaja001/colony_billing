<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;
use App\Http\Controllers\Billing\BillingDraftController;

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

    Route::view('/ui/dashboard', 'auth.protected-shell');
    Route::view('/ui/reports', 'auth.protected-shell');
    Route::view('/ui/reconciliation', 'auth.protected-shell');
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN'])->group(function () {
    Route::view('/ui/admin/users', 'auth.protected-shell');
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    // Explicit scope wall: protected placeholders only.
    Route::get('/ui/billing', fn () => response()->view('auth.blocked-domain', [], 423));
    Route::get('/ui/month-cycle', fn () => response()->view('auth.blocked-domain', [], 423));
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN', 'month.guard.shell'])->group(function () {
    // Guard-shell routes only. No month domain logic implemented.
    Route::post('/month/open', fn () => response()->json(['status' => 'ok', 'route' => '/month/open', 'mode' => 'guard-shell-pass']));
    Route::post('/month/transition', fn () => response()->json(['status' => 'ok', 'route' => '/month/transition', 'mode' => 'guard-shell-exception-pass']));

    // Billing core endpoints currently in migration.
    Route::post('/api/billing/precheck', [BillingDraftController::class, 'precheck']);
    Route::post('/api/billing/finalize', [BillingDraftController::class, 'finalize']);
    Route::post('/billing/lock', [BillingDraftController::class, 'lock']);
    Route::post('/billing/approve', [BillingDraftController::class, 'approve']);

    // Adjustments / recovery (evidence shows removed/disabled flow -> explicit real 410 parity behavior).
    Route::post('/billing/adjustments/create', [BillingDraftController::class, 'adjustmentCreate']);
    Route::post('/billing/adjustments/approve', [BillingDraftController::class, 'adjustmentApprove']);
    Route::post('/recovery/payment', [BillingDraftController::class, 'recoveryPayment']);
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
