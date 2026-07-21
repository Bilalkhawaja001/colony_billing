<?php

use App\Http\Controllers\Billing\V2\PeopleResidencyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])
    ->prefix('api/v2/people-residency')
    ->group(function () {
        Route::get('/employees', [PeopleResidencyController::class, 'employees']);
        Route::get('/profiles/{companyId}', [PeopleResidencyController::class, 'profile']);
        Route::get('/families', [PeopleResidencyController::class, 'families']);
        Route::get('/occupancy', [PeopleResidencyController::class, 'occupancy']);
        Route::get('/registry/{companyId}', [PeopleResidencyController::class, 'registryGet']);
        Route::get('/residence-types', [PeopleResidencyController::class, 'residenceTypes']);
        Route::get('/colonies', [PeopleResidencyController::class, 'colonies']);
        Route::get('/blocks/{colony}', [PeopleResidencyController::class, 'blocks']);
        Route::get('/rooms/{colony}/{block}', [PeopleResidencyController::class, 'rooms']);
    });

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY'])
    ->prefix('api/v2/people-residency')
    ->group(function () {
        Route::post('/employees', [PeopleResidencyController::class, 'createEmployee']);
        Route::patch('/employees/{companyId}', [PeopleResidencyController::class, 'updateEmployee']);
        Route::post('/employees/import', [PeopleResidencyController::class, 'importEmployees']);
        Route::post('/profiles/{companyId}/family-members', [PeopleResidencyController::class, 'createFamilyMember']);
        Route::post('/profiles/{companyId}/residence/assign', [PeopleResidencyController::class, 'assignResidence']);
        Route::post('/profiles/{companyId}/residence/shift', [PeopleResidencyController::class, 'shiftResidence']);
        Route::post('/profiles/{companyId}/residence/vacate', [PeopleResidencyController::class, 'vacateResidence']);
        Route::post('/registry/upsert', [PeopleResidencyController::class, 'registryUpsert']);
        Route::post('/registry/import-preview', [PeopleResidencyController::class, 'registryPreview']);
        Route::post('/registry/import-commit', [PeopleResidencyController::class, 'registryCommit']);
    });
