<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;

/*
|--------------------------------------------------------------------------
| LIMITED GO ROUTES (Auth/RBAC shell only)
|--------------------------------------------------------------------------
| Billing/month/reconciliation/adjustments/electric_v1 are intentionally
| blocked in this batch. Protected area routes are placeholders only.
*/

Route::get('/login', [AuthDraftController::class, 'showLogin']);
Route::post('/login', [AuthDraftController::class, 'login']);
Route::get('/logout', [AuthDraftController::class, 'logout']);

Route::get('/forgot-password', [AuthDraftController::class, 'showForgotPassword']);
Route::post('/forgot-password', [AuthDraftController::class, 'forgotPassword']);

Route::get('/reset-password', [AuthDraftController::class, 'showResetPassword']);
Route::post('/reset-password', [AuthDraftController::class, 'resetPassword']);

Route::middleware(['ensure.auth', 'force.password.change'])->group(function () {
    Route::get('/ui/profile', [AuthDraftController::class, 'showProfile']);
    Route::post('/api/profile/change-password', [AuthDraftController::class, 'changePassword']);

    // Protected shell placeholders (no business logic in this batch)
    Route::view('/ui/dashboard', 'auth/protected-shell');
    Route::view('/ui/reports', 'auth/protected-shell');
    Route::view('/ui/reconciliation', 'auth/protected-shell');
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN'])->group(function () {
    Route::view('/ui/admin/users', 'auth/protected-shell');
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN'])->group(function () {
    // Explicitly blocked domain actions in LIMITED GO batch
    Route::view('/billing/lock', 'auth/blocked-domain');
    Route::view('/month/open', 'auth/blocked-domain');
});
