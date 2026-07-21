<?php
use App\Http\Controllers\Billing\MonthlyActiveDaysController;
use Illuminate\Support\Facades\Route;
Route::middleware(['ensure.auth','force.password.change'])->group(function () {
    Route::get('/active-days-monthly', [MonthlyActiveDaysController::class, 'index']);
    Route::get('/active-days-monthly/template', [MonthlyActiveDaysController::class, 'template']);
    Route::get('/active-days-monthly/rows', [MonthlyActiveDaysController::class, 'rows']);
    Route::post('/active-days-monthly/preview', [MonthlyActiveDaysController::class, 'preview']);
    Route::post('/active-days-monthly/import', [MonthlyActiveDaysController::class, 'import']);
});
