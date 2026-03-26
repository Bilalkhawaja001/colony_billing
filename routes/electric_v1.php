<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectricV1\ElectricV1Controller;

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY'])->group(function () {
    Route::post('/api/electric-v1/run', [ElectricV1Controller::class, 'run']);

    Route::post('/api/electric-v1/input/allowance/upsert', [ElectricV1Controller::class, 'upsertAllowance']);
    Route::post('/api/electric-v1/input/readings/upsert', [ElectricV1Controller::class, 'upsertReadings']);
    Route::post('/api/electric-v1/input/attendance/upsert', [ElectricV1Controller::class, 'upsertAttendance']);
    Route::post('/api/electric-v1/input/occupancy/upsert', [ElectricV1Controller::class, 'upsertOccupancy']);
    Route::post('/api/electric-v1/input/adjustments/upsert', [ElectricV1Controller::class, 'upsertAdjustments']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,VIEWER'])->group(function () {
    Route::get('/api/electric-v1/outputs', [ElectricV1Controller::class, 'outputs']);
    Route::get('/api/electric-v1/exceptions', [ElectricV1Controller::class, 'exceptions']);
    Route::get('/api/electric-v1/runs', [ElectricV1Controller::class, 'runs']);

    Route::view('/ui/electric-v1-run', 'electric_v1.run');
    Route::view('/ui/electric-v1-outputs', 'electric_v1.outputs');
});
