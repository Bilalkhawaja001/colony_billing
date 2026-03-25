<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;

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

Route::middleware(['ensure.auth', 'force.password.change', 'month.guard.shell', 'role:SUPER_ADMIN,BILLING_ADMIN'])->group(function () {
    // Intentionally blocked domain actions in LIMITED GO batch.
    Route::post('/billing/lock', fn () => response()->json(['status' => 'error', 'error' => 'blocked in LIMITED GO auth-only batch'], 423));
    Route::post('/month/open', fn () => response()->json(['status' => 'error', 'error' => 'blocked in LIMITED GO auth-only batch'], 423));
});
