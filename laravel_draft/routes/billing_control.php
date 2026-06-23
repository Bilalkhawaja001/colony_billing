<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ControlRoomAuthGuard;
use App\Http\Controllers\Billing\ControlRoom\ControlRoomController;
use App\Http\Controllers\Billing\ControlRoom\ReadinessController;
use App\Http\Controllers\Billing\ControlRoom\MeterReadingController;
use App\Http\Controllers\Billing\ControlRoom\RoomAssignmentController;
use App\Http\Controllers\Billing\ControlRoom\BillRunController;
use App\Http\Controllers\Billing\ControlRoom\ExportController;

/*
|--------------------------------------------------------------------------
| Colony Billing Control Room
|--------------------------------------------------------------------------
| App is already mounted at /billing by webroot.
| Do NOT add Route::prefix('billing') here.
*/

Route::middleware([ControlRoomAuthGuard::class])
    ->prefix('control-room')
    ->as('billing.control.')
    ->group(function () {
        Route::get('/', [ControlRoomController::class, 'index'])->name('home');

        Route::get('/readiness', [ReadinessController::class, 'index'])->name('readiness');

        Route::get('/readings', [MeterReadingController::class, 'index'])->name('readings');
        Route::post('/readings', [MeterReadingController::class, 'save'])->name('readings.save');

        Route::get('/rooms', [RoomAssignmentController::class, 'index'])->name('rooms');
        Route::post('/rooms', [RoomAssignmentController::class, 'save'])->name('rooms.save');

        Route::get('/generate', [BillRunController::class, 'index'])->name('generate');
        Route::post('/generate', [BillRunController::class, 'store'])->name('generate.store');

        Route::get('/runs/{run}/status', [BillRunController::class, 'status'])->name('runs.status');
        Route::get('/runs/{run}', [BillRunController::class, 'show'])->name('runs.show');
        Route::get('/runs/{run}/row/{row}', [BillRunController::class, 'row'])->name('runs.row');

        Route::get('/export', [ExportController::class, 'index'])->name('export');
        Route::post('/export/download', [ExportController::class, 'download'])->name('export.download');
    });
