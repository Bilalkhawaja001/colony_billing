<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;
use App\Http\Controllers\Auth\SsoConsumeController;
use App\Http\Controllers\Billing\BillingDraftController;
use App\Http\Controllers\Billing\ImportsMonthlySetupController;
use App\Http\Controllers\Billing\MasterDataDraftController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Billing\FamilyRegistryResultsController;
use App\Http\Controllers\Billing\EmployeesMeterParityController;
use App\Http\Controllers\Ui\ParityUiController;
use App\Http\Controllers\Infra\InfraController;
use App\Http\Controllers\Billing\UnitReferenceParityController;
use App\Http\Controllers\Transport\TransportController;
use App\Http\Controllers\Billing\V2\BillRunPreflightController;
use App\Http\Controllers\Billing\V2\BillRunGateController;

// Phase 5A legacy duplicate page redirects (temporary 302, query string preserved).
Route::get('/ui/employees', function (\Illuminate\Http\Request $request) {
    $target = url('people-residency');
    if ($request->getQueryString()) { $target .= '?' . $request->getQueryString(); }
    return redirect()->to($target, 302);
});
Route::get('/ui/masters/rates', function (\Illuminate\Http\Request $request) {
    $target = url('rates');
    if ($request->getQueryString()) { $target .= '?' . $request->getQueryString(); }
    return redirect()->to($target, 302);
});
Route::get('/ui/transport', function (\Illuminate\Http\Request $request) {
    $target = url('transport');
    if ($request->getQueryString()) { $target .= '?' . $request->getQueryString(); }
    return redirect()->to($target, 302);
});

Route::get('/health', [InfraController::class, 'health']);

Route::get('/', [ParityUiController::class, 'home']);

Route::get('/login', [AuthDraftController::class, 'showLogin']);
Route::post('/login', [AuthDraftController::class, 'login']);
Route::get('/logout', [AuthDraftController::class, 'logout']);
Route::get('/sso/consume', SsoConsumeController::class);

Route::get('/forgot-password', [AuthDraftController::class, 'showForgotPassword']);
Route::post('/forgot-password', [AuthDraftController::class, 'forgotPassword']);

Route::get('/reset-password', [AuthDraftController::class, 'showResetPassword']);
Route::post('/reset-password', [AuthDraftController::class, 'resetPassword']);

Route::middleware(['ensure.auth', 'force.password.change', 'shell.rbac'])->group(function () {
    Route::get('/profile', [AuthDraftController::class, 'showProfile']);
    Route::get('/ui/profile', fn () => redirect('/profile'));
    Route::post('/api/profile/change-password', [AuthDraftController::class, 'changePassword']);

    Route::get('/dashboard', [ParityUiController::class, 'dashboardV2']);
    Route::get('/dashboard-v2', [ParityUiController::class, 'dashboardV2']);
    Route::get('/imports-validation', [ParityUiController::class, 'imports']);
    Route::get('/reporting', [ParityUiController::class, 'reports']);
    Route::get('/people-residency', [ParityUiController::class, 'employeeMaster']);
    Route::post('/people-residency/residence-status', [\App\Http\Controllers\Billing\UnitDirectoryController::class, 'setResidenceStatus'])->name('billing.residence.status');
    Route::get('/unit-directory', [ParityUiController::class, 'unitMaster']);
    Route::get('/family-list', [ParityUiController::class, 'familyList']);
    Route::get('/family-list/export', [\App\Http\Controllers\Billing\FamilyExportController::class, 'export']);
    Route::get('/staff-check', [ParityUiController::class, 'staffCheck']);
    Route::post('/staff-check/compare', [ParityUiController::class, 'staffCheckCompare']);
    Route::post('/staff-check/action', [ParityUiController::class, 'staffCheckAction']);
    Route::get('/employee-profile/{companyId}', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'show']);
    Route::post('/employee-profile/{companyId}/family-members', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'storeFamilyMember']);
    Route::post('/employee-profile/{companyId}/family-members/{familyMemberId}', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'updateFamilyMember']);
    Route::post('/employee-profile/{companyId}/family-members/{familyMemberId}/movement', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'recordFamilyMovement']);
    Route::post('/employee-profile/{companyId}/residence/assign', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'assignResidence']);
    Route::post('/employee-profile/{companyId}/residence/shift', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'shiftResidence']);
    Route::post('/employee-profile/{companyId}/residence/vacate', [\App\Http\Controllers\Billing\EmployeeProfileController::class, 'vacateResidence']);
    Route::get('/transport', function (\Illuminate\Http\Request $request) {
        return view('ui.transport', [
            'monthCycle' => (string) ($request->query('month_cycle') ?? ''),
        ]);
    });
    // Hub: Meters & Readings (single sidebar entry)
    Route::get('/meters-readings', [ParityUiController::class, 'metersHub']);

    // Workspaces under the hub (no separate sidebar entries)
    Route::get('/meters-readings/registry', [ParityUiController::class, 'meterRegistry']);
    Route::get('/meters-readings/readings', [ParityUiController::class, 'meterReadings']);
    Route::get('/meters-readings/readings/analysis-data', [ParityUiController::class, 'meterReadingsAnalysisData']);
    Route::get('/meters-readings/water-tools', [ParityUiController::class, 'waterTools']);
    Route::get('/housing-rooms', [ParityUiController::class, 'rooms']);
    Route::get('/housing-occupancy', [ParityUiController::class, 'occupancy']);
    Route::get('/electric-v1-lab/outputs', fn () => redirect('/ui/electric-v1-outputs'));
    Route::get('/electric-v1-lab/run', fn () => redirect('/ui/electric-v1-run'));

    // Backward-compatible /ui redirects
    Route::get('/ui/dashboard', fn () => redirect('/dashboard'));
    Route::get('/ui/imports', fn () => redirect('/imports-validation'));
    Route::get('/ui/reports', fn () => redirect('/reporting'));
    Route::get('/ui/reconciliation', fn () => redirect('/reporting'));
    Route::get('/ui/results/employee-wise', fn () => redirect('/reporting'));
    Route::get('/ui/results/unit-wise', fn () => redirect('/reporting'));
    Route::get('/ui/logs', fn () => redirect('/reporting'));
    Route::get('/ui/employee-master', fn () => redirect('/people-residency'));
    Route::get('/ui/employee-helper', fn () => redirect('/people-residency'));
    Route::get('/ui/inputs/hr', fn () => redirect('/people-residency'));
    Route::get('/ui/unit-master', fn () => redirect('/unit-directory'));
    // Backward-compatible /ui redirects (meter domain -> new hub/workspaces)
    Route::get('/ui/meter-master', fn () => redirect('/meters-readings/registry'));
    Route::get('/ui/masters/meters', fn () => redirect('/meters-readings/registry'));

    Route::get('/ui/inputs/readings', fn () => redirect('/meters-readings/readings'));

    Route::get('/ui/water-meters', fn () => redirect('/meters-readings/water-tools'));
    Route::get('/ui/inputs/ro', fn () => redirect('/meters-readings/water-tools'));
    Route::get('/ui/meter-register-ingest', fn () => redirect('/imports-validation'));
    Route::get('/ui/rooms', fn () => redirect('/housing-rooms'));
    Route::get('/ui/occupancy', fn () => redirect('/housing-occupancy'));
    Route::get('/ui/month-control', fn () => redirect('/month-lifecycle'));
    Route::get('/ui/finalized-months', fn () => redirect('/month-lifecycle'));
    Route::get('/ui/monthly-setup', fn () => redirect('/month-lifecycle'));
    Route::get('/ui/family-details', fn () => redirect('/reporting'));
    Route::get('/ui/elec-summary', fn () => redirect('/reporting'));
    Route::get('/ui/van', fn () => redirect('/reporting'));
    Route::get('/ui/rates', fn () => redirect('/rates'));

    // Legacy shell aliases now point to canonical modules
    Route::get('/ui/masters/employees', fn () => redirect('/people-residency'));
    Route::get('/ui/masters/units', fn () => redirect('/unit-directory'));
    Route::get('/ui/masters/meters', fn () => redirect('/meters-readings'));
    Route::get('/ui/inputs/mapping', fn () => redirect('/housing-occupancy'));

    Route::get('/rates', [ParityUiController::class, 'rates']);

    Route::get('/api/dashboard/colony-kpis', [ParityUiController::class, 'colonyKpis']);
    Route::get('/api/dashboard/family-members', [ParityUiController::class, 'familyMembers']);
    Route::get('/api/dashboard/van-kids', [ParityUiController::class, 'vanKids']);
    Route::get('/api/transport/summary', [TransportController::class, 'summary']);
    Route::get('/api/transport/export/csv', [TransportController::class, 'exportCsv']);
    Route::get('/api/transport/child-month-usage', [TransportController::class, 'childMonthUsage']);
    Route::post('/api/transport/child-month-usage/upsert', [TransportController::class, 'childMonthUsageUpsert']);
    Route::post('/api/transport/month-cycle/upsert', [\App\Http\Controllers\Transport\TransportController::class, 'monthCycleUpsert']);
    Route::post('/api/transport/vehicles/upsert', [TransportController::class, 'vehicleUpsert']);
    Route::post('/api/transport/rent-entries/upsert', [TransportController::class, 'rentEntryUpsert']);
    Route::post('/api/transport/fuel-entries/upsert', [TransportController::class, 'fuelEntryUpsert']);
    Route::post('/api/transport/adjustments/upsert', [TransportController::class, 'adjustmentUpsert']);

    // School Van enrolment routes used by resources/views/ui/transport.blade.php
    Route::get('/api/transport/school-van/enrolments', [\App\Http\Controllers\Transport\TransportController::class, 'schoolVanEnrolments']);
    Route::post('/api/transport/school-van/enrolments/add', [\App\Http\Controllers\Transport\TransportController::class, 'schoolVanEnrolmentAdd']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/left', [\App\Http\Controllers\Transport\TransportController::class, 'schoolVanEnrolmentLeave']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/cancel', [\App\Http\Controllers\Transport\TransportController::class, 'schoolVanEnrolmentCancel']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/reactivate', [\App\Http\Controllers\Transport\TransportController::class, 'schoolVanEnrolmentReactivate']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/restore-cancellation', [\App\Http\Controllers\Transport\TransportController::class, 'schoolVanEnrolmentRestoreCancellation']);
    Route::post('/api/transport/school-van/bill/generate', [\App\Http\Controllers\Transport\TransportController::class, 'generateSchoolVanBill']);

});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN'])->group(function () {
    Route::get('/ui/admin/users', [AdminUsersController::class, 'index']);
    Route::post('/api/admin/users/create', [AdminUsersController::class, 'create']);
    Route::post('/api/admin/users/update', [AdminUsersController::class, 'update']);
    Route::post('/api/admin/users/reset-password', [AdminUsersController::class, 'resetPassword']);
    Route::get('/api/logs', [FamilyRegistryResultsController::class, 'logs']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::get('/billing-run-lock', [ParityUiController::class, 'billing']);
    Route::get('/month-lifecycle', [ParityUiController::class, 'monthCycle']);
    Route::get('/api/v2/bill-runs/preflight', [BillRunPreflightController::class, 'show']);
    Route::get('/api/v2/bill-runs/gates', [BillRunGateController::class, 'show']);

    // Backward-compatible /ui redirects
    Route::get('/ui/billing', fn () => redirect('/billing-run-lock'));
    Route::get('/ui/month-cycle', fn () => redirect('/month-lifecycle'));
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN', 'month.guard.shell'])->group(function () {
    Route::post('/month/open', [ImportsMonthlySetupController::class, 'monthOpen']);
    Route::post('/month/transition', [ImportsMonthlySetupController::class, 'monthTransition']);

    // Billing core endpoints currently in migration.
    Route::post('/api/billing/precheck', [BillingDraftController::class, 'precheck']);
    Route::post('/api/billing/finalize', [BillingDraftController::class, 'finalize']);
    Route::post('/billing/elec/compute', [BillingDraftController::class, 'elecCompute']);
    Route::post('/billing/water/compute', [BillingDraftController::class, 'waterCompute']);
    Route::post('/billing/run', [BillingDraftController::class, 'run']);
    Route::post('/billing/fingerprint', [BillingDraftController::class, 'fingerprint']);
    Route::get('/billing/fingerprint', [BillingDraftController::class, 'fingerprint']);
    Route::get('/billing/adjustments/list', [BillingDraftController::class, 'adjustmentsList']);
    Route::get('/billing/print/{month_cycle}/{employee_id}', [BillingDraftController::class, 'printEmployee']);
    Route::post('/billing/lock', [BillingDraftController::class, 'lock']);
    Route::post('/billing/approve', [BillingDraftController::class, 'approve']);
    Route::post('/api/v2/bill-runs/preflight/save', [BillRunPreflightController::class, 'save']);
    Route::post('/api/v2/bill-runs/gates/transition', [BillRunGateController::class, 'transition']);

    // Adjustments / recovery (evidence shows removed/disabled flow -> explicit real 410 parity behavior).
    Route::post('/billing/adjustments/create', [BillingDraftController::class, 'adjustmentCreate']);
    Route::post('/billing/adjustments/approve', [BillingDraftController::class, 'adjustmentApprove']);
    Route::post('/recovery/payment', [BillingDraftController::class, 'recoveryPayment']);

    Route::post('/imports/meter-register/ingest-preview', [ImportsMonthlySetupController::class, 'ingestPreview']);
    Route::post('/imports/mark-validated', [ImportsMonthlySetupController::class, 'markValidated']);
    Route::post('/monthly-rates/initialize', [ImportsMonthlySetupController::class, 'monthlyRatesInitialize']);
    Route::post('/monthly-rates/config/upsert', [ImportsMonthlySetupController::class, 'monthlyRatesConfigUpsert']);
    Route::post('/rates/upsert', [ImportsMonthlySetupController::class, 'ratesUpsert']);
    Route::post('/rates/approve', [ImportsMonthlySetupController::class, 'ratesApprove']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::get('/units', [MasterDataDraftController::class, 'units']);
    Route::get('/units/suggest', [UnitReferenceParityController::class, 'suggest']);
    Route::get('/units/resolve/{unit_id}', [UnitReferenceParityController::class, 'resolve']);
    Route::get('/api/units/reference', [UnitReferenceParityController::class, 'index']);
    Route::get('/api/units/reference/{unit_id>', [UnitReferenceParityController::class, 'show']);
    Route::get('/api/units/reference/<unit_id>', [UnitReferenceParityController::class, 'show']);
    Route::get('/api/units/reference/cascade', [UnitReferenceParityController::class, 'cascade']);
    Route::get('/rooms', [MasterDataDraftController::class, 'rooms']);
    Route::get('/occupancy/context', [MasterDataDraftController::class, 'occupancyContext']);
    Route::get('/occupancy', [MasterDataDraftController::class, 'occupancy']);

    Route::get('/employees', [EmployeesMeterParityController::class, 'employees']);
    Route::get('/employees/search', [EmployeesMeterParityController::class, 'employeesSearch']);
    Route::get('/employees/meta/departments', [EmployeesMeterParityController::class, 'employeesDepartments']);
    Route::get('/employees/{companyId}', [EmployeesMeterParityController::class, 'employeeGet']);
    Route::get('/employees/{company_id}', [EmployeesMeterParityController::class, 'employeeGetCompat']);
    Route::get('/meter-reading/latest/{unitId}', [EmployeesMeterParityController::class, 'meterReadingLatest']);
    Route::get('/meter-reading/latest/{unit_id>', [EmployeesMeterParityController::class, 'meterReadingLatestCompat']);
    Route::get('/meter-reading/latest/<unit_id>', [EmployeesMeterParityController::class, 'meterReadingLatestCompat']);
    Route::get('/meter-unit', [EmployeesMeterParityController::class, 'meterUnit']);

    Route::get('/api/water/occupancy-snapshot', [BillingDraftController::class, 'waterOccupancySnapshot']);
    Route::get('/api/water/zone-adjustments', [BillingDraftController::class, 'waterZoneAdjustmentsGet']);
    Route::get('/api/water/allocation-preview', [BillingDraftController::class, 'waterAllocationPreview']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY'])->group(function () {
    Route::post('/units/upsert', [MasterDataDraftController::class, 'unitsUpsert']);
    Route::delete('/units/{unitId}', [MasterDataDraftController::class, 'unitsDelete']);
    Route::delete('/units/{unit_id}', [MasterDataDraftController::class, 'unitsDeleteCompat']);
    Route::delete('/units/<unit_id>', [MasterDataDraftController::class, 'unitsDeleteCompat']);
    Route::post('/api/units/reference/upsert', [UnitReferenceParityController::class, 'upsert']);

    Route::post('/rooms/upsert', [MasterDataDraftController::class, 'roomsUpsert']);
    Route::delete('/rooms/{id}', [MasterDataDraftController::class, 'roomsDelete']);
    Route::delete('/rooms/{row_id}', [MasterDataDraftController::class, 'roomsDeleteCompat']);
    Route::delete('/rooms/<row_id>', [MasterDataDraftController::class, 'roomsDeleteCompat']);
    Route::delete('/rooms/<int:row_id>', [MasterDataDraftController::class, 'roomsDeleteCompat']);

    Route::post('/occupancy/upsert', [MasterDataDraftController::class, 'occupancyUpsert']);
    Route::delete('/occupancy/{id}', [MasterDataDraftController::class, 'occupancyDelete']);
    Route::delete('/occupancy/{row_id>', [MasterDataDraftController::class, 'occupancyDeleteCompat']);
    Route::delete('/occupancy/<row_id>', [MasterDataDraftController::class, 'occupancyDeleteCompat']);
    Route::delete('/occupancy/<int:row_id>', [MasterDataDraftController::class, 'occupancyDeleteCompat']);
    Route::post('/api/occupancy/autofill', [MasterDataDraftController::class, 'occupancyAutofill']);
    Route::post('/api/water/zone-adjustments', [BillingDraftController::class, 'waterZoneAdjustmentsUpsert']);

    Route::post('/employees/import', [EmployeesMeterParityController::class, 'employeesImport']);
    Route::post('/imports/active-days/import', [EmployeesMeterParityController::class, 'activeDaysImport']);
    Route::post('/employees/upsert', [EmployeesMeterParityController::class, 'employeesUpsert']);
    Route::post('/employees/add', [EmployeesMeterParityController::class, 'employeesAdd']);
    Route::patch('/employees/{companyId>', [EmployeesMeterParityController::class, 'employeePatch']);
    Route::patch('/employees/{company_id>', [EmployeesMeterParityController::class, 'employeePatchCompat']);
    Route::delete('/employees/{companyId>', [EmployeesMeterParityController::class, 'employeeDelete']);
    Route::delete('/employees/{company_id}', [EmployeesMeterParityController::class, 'employeeDeleteCompat']);

    Route::post('/meter-reading/upsert', [EmployeesMeterParityController::class, 'meterReadingUpsert']);
    Route::post('/meter-unit/upsert', [EmployeesMeterParityController::class, 'meterUnitUpsert']);

    Route::post('/api/rooms/cascade', [EmployeesMeterParityController::class, 'roomsCascade']);
    Route::get('/api/rooms/cascade', [EmployeesMeterParityController::class, 'roomsCascade']);

    Route::post('/family/details/upsert', [FamilyRegistryResultsController::class, 'familyDetailsUpsert']);
    Route::post('/registry/employees/upsert', [FamilyRegistryResultsController::class, 'registryEmployeesUpsert']);
    Route::post('/registry/employees/import-preview', [FamilyRegistryResultsController::class, 'registryEmployeesImportPreview']);
    Route::post('/registry/employees/import-commit', [FamilyRegistryResultsController::class, 'registryEmployeesImportCommit']);
    Route::post('/registry/employees/promote-to-master', [FamilyRegistryResultsController::class, 'registryEmployeesPromoteToMaster']);
    Route::post('/expenses/monthly-variable/upsert', [ImportsMonthlySetupController::class, 'monthlyVariableUpsert']);
    Route::post('/expenses/monthly-variable', [ImportsMonthlySetupController::class, 'monthlyVariableUpsert']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::get('/imports/unit-id-aliases', [ImportsMonthlySetupController::class, 'unitIdAliases']);
    Route::get('/imports/error-report/{token}', [ImportsMonthlySetupController::class, 'errorReport']);
    Route::get('/monthly-rates/config', [ImportsMonthlySetupController::class, 'monthlyRatesConfig']);
    Route::get('/monthly-rates/history', [ImportsMonthlySetupController::class, 'monthlyRatesHistory']);
    Route::get('/expenses/monthly-variable', [ImportsMonthlySetupController::class, 'monthlyVariableGet']);

    Route::get('/family/details/context', [FamilyRegistryResultsController::class, 'familyDetailsContext']);
    Route::get('/family/details', [FamilyRegistryResultsController::class, 'familyDetails']);
    Route::get('/registry/employees/{companyId}', [FamilyRegistryResultsController::class, 'registryEmployeeGet']);
    Route::get('/api/results/employee-wise', [FamilyRegistryResultsController::class, 'resultsEmployeeWise']);
    Route::get('/api/results/unit-wise', [FamilyRegistryResultsController::class, 'resultsUnitWise']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,VIEWER'])->group(function () {
    Route::get('/reports/reconciliation', [BillingDraftController::class, 'reconciliationReport']);
    Route::get('/reports/monthly-summary', [BillingDraftController::class, 'monthlySummary']);
    Route::get('/reports/recovery', [BillingDraftController::class, 'recoveryReport']);
    Route::get('/reports/employee-bill-summary', [BillingDraftController::class, 'employeeBillSummary']);
    Route::get('/reports/van', [BillingDraftController::class, 'vanReport']);
    Route::get('/reports/employee-statement', [\App\Http\Controllers\Billing\DataGridController::class, 'employeeStatement']);
    Route::get('/statement-v2', [\App\Http\Controllers\Billing\StatementV2Controller::class, 'show'])->name('billing.statement.v2');
    Route::post('/readings/import/preview', [\App\Http\Controllers\Billing\ReadingImportController::class, 'preview'])->name('billing.readings.import.preview');
    Route::post('/readings/import/commit', [\App\Http\Controllers\Billing\ReadingImportController::class, 'commit'])->name('billing.readings.import.commit');
    Route::get('/bulk-leave', [\App\Http\Controllers\Billing\EmployeeStatusController::class, 'bulkLeaveForm'])->name('billing.emp.bulkleave.form');
    Route::post('/bulk-leave', [\App\Http\Controllers\Billing\EmployeeStatusController::class, 'bulkLeave'])->name('billing.emp.bulkleave');
    Route::post('/employees/{companyId}/leave', [\App\Http\Controllers\Billing\EmployeeStatusController::class, 'setLeave'])->name('billing.emp.leave');
    Route::post('/employees/{companyId}/reactivate', [\App\Http\Controllers\Billing\EmployeeStatusController::class, 'reactivate'])->name('billing.emp.reactivate');
    Route::get('/pending-employees', [\App\Http\Controllers\Billing\PendingEmployeeController::class, 'index'])->name('billing.pending.index');
    Route::post('/pending-employees/{companyId}/approve', [\App\Http\Controllers\Billing\PendingEmployeeController::class, 'approve'])->name('billing.pending.approve');
    Route::delete('/pending-employees/{companyId}', [\App\Http\Controllers\Billing\PendingEmployeeController::class, 'reject'])->name('billing.pending.reject');
    Route::post('/unit-directory/room', [\App\Http\Controllers\Billing\UnitDirectoryController::class, 'storeRoom'])->name('billing.units.room.store');
    Route::patch('/unit-directory/room/{id}/toggle', [\App\Http\Controllers\Billing\UnitDirectoryController::class, 'toggleRoom'])->name('billing.units.room.toggle');
    Route::post('/unit-directory', [\App\Http\Controllers\Billing\UnitDirectoryController::class, 'store'])->name('billing.units.store');
    Route::put('/unit-directory/{unitId}', [\App\Http\Controllers\Billing\UnitDirectoryController::class, 'update'])->name('billing.units.update');
    Route::patch('/unit-directory/{unitId}/toggle', [\App\Http\Controllers\Billing\UnitDirectoryController::class, 'toggle'])->name('billing.units.toggle');
    Route::get('/asset-categories', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'index'])->name('billing.assets.categories');
    Route::post('/asset-categories', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'storeCategory'])->name('billing.assets.category.store');
    Route::put('/asset-categories/{id}', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'updateCategory'])->name('billing.assets.category.update');
    Route::delete('/asset-categories/{id}', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'deleteCategory'])->name('billing.assets.category.delete');
    Route::post('/asset-master', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'storeAsset'])->name('billing.assets.master.store');
    Route::delete('/asset-master/{id}', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'deleteAsset'])->name('billing.assets.master.delete');
    Route::post('/asset-categories/item', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'storeItem'])->name('billing.assets.item.store');
    Route::delete('/asset-categories/item/{id}', [\App\Http\Controllers\Billing\AssetCategoryController::class, 'deleteItem'])->name('billing.assets.item.delete');
    Route::get('/allowances', [\App\Http\Controllers\Billing\AllowanceController::class, 'index'])->name('billing.allowances');
    Route::post('/allowances/import/preview', [\App\Http\Controllers\Billing\AllowanceController::class, 'importPreview'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.import.preview');
    Route::post('/allowances/import/commit', [\App\Http\Controllers\Billing\AllowanceController::class, 'importCommit'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.import.commit');
    Route::post('/allowances/import/cancel', [\App\Http\Controllers\Billing\AllowanceController::class, 'importCancel'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.import.cancel');
    Route::post('/allowances', [\App\Http\Controllers\Billing\AllowanceController::class, 'store'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.store');
    Route::put('/allowances/{allowance}', [\App\Http\Controllers\Billing\AllowanceController::class, 'update'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.update');
    Route::patch('/allowances/{allowance}/status', [\App\Http\Controllers\Billing\AllowanceController::class, 'toggleStatus'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.status');
    Route::put('/allowances/rooms/{roomAllowance}', [\App\Http\Controllers\Billing\AllowanceController::class, 'updateRoom'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.rooms.update');
    Route::patch('/allowances/rooms/{roomAllowance}/status', [\App\Http\Controllers\Billing\AllowanceController::class, 'toggleRoomStatus'])
        ->middleware('role:SUPER_ADMIN,BILLING_ADMIN')
        ->name('billing.allowances.rooms.status');
    Route::get('/reports/employee-statement/print', [\App\Http\Controllers\Billing\DataGridController::class, 'employeeStatementPrint']);
    Route::get('/reports/employee-statement/export', [\App\Http\Controllers\Billing\DataGridController::class, 'employeeStatementExport']);
    Route::get('/reports/employee-statements/export-all', [\App\Http\Controllers\Billing\DataGridController::class, 'employeeStatementsExportAll']);
    Route::get('/reports/elec-summary', [BillingDraftController::class, 'elecSummary']);
    Route::get('/export/excel/reconciliation', [BillingDraftController::class, 'exportExcelReconciliation']);
    Route::get('/export/excel/monthly-summary', [BillingDraftController::class, 'exportExcelMonthlySummary']);
    Route::get('/export/pdf/monthly-summary', [BillingDraftController::class, 'exportPdfMonthlySummary']);
});

require __DIR__.'/people_residency_v2.php';
require __DIR__.'/electric_v1.php';

// BEGIN billing-control-route-loader
if (file_exists(__DIR__ . '/billing_control.php')) {
    require __DIR__ . '/billing_control.php';
}
// END billing-control-route-loader

require __DIR__.'/monthly_active_days.php';
