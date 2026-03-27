<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;
use App\Http\Controllers\Billing\BillingDraftController;
use App\Http\Controllers\Billing\ImportsMonthlySetupController;
use App\Http\Controllers\Billing\MasterDataDraftController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Billing\FamilyRegistryResultsController;
use App\Http\Controllers\Billing\EmployeesMeterParityController;
use App\Http\Controllers\Ui\ParityUiController;
use App\Http\Controllers\Infra\InfraController;
use App\Http\Controllers\Billing\UnitReferenceParityController;

Route::get('/health', [InfraController::class, 'health']);

Route::get('/', [ParityUiController::class, 'home']);

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

    Route::get('/ui/dashboard', [ParityUiController::class, 'dashboard']);
    Route::get('/ui/reports', [ParityUiController::class, 'reports']);
    Route::get('/ui/reconciliation', [ParityUiController::class, 'reconciliation']);
    Route::get('/ui/elec-summary', [ParityUiController::class, 'elecSummary']);
    Route::get('/ui/month-control', [ParityUiController::class, 'monthControl']);
    Route::get('/ui/monthly-setup', [ParityUiController::class, 'monthControl']);
    Route::get('/ui/imports', [ParityUiController::class, 'imports']);

    Route::get('/ui/rates', [ParityUiController::class, 'rates']);
    Route::get('/ui/water-meters', [ParityUiController::class, 'waterMeters']);
    Route::get('/ui/van', [ParityUiController::class, 'van']);
    Route::get('/ui/employee-master', [ParityUiController::class, 'employeeMaster']);
    Route::get('/ui/employees', [ParityUiController::class, 'employees']);
    Route::get('/ui/employee-helper', [ParityUiController::class, 'employeeHelper']);
    Route::get('/ui/unit-master', [ParityUiController::class, 'unitMaster']);
    Route::get('/ui/meter-master', [ParityUiController::class, 'meterMaster']);
    Route::get('/ui/meter-register-ingest', [ParityUiController::class, 'meterRegisterIngest']);
    Route::get('/ui/rooms', [ParityUiController::class, 'rooms']);
    Route::get('/ui/occupancy', [ParityUiController::class, 'occupancy']);
    Route::get('/ui/electric-v1-run', [ParityUiController::class, 'electricV1Run']);
    Route::get('/ui/electric-v1-outputs', [ParityUiController::class, 'electricV1Outputs']);
    Route::get('/ui/masters/employees', [ParityUiController::class, 'mastersEmployees']);
    Route::get('/ui/masters/units', [ParityUiController::class, 'mastersUnits']);
    Route::get('/ui/masters/meters', [ParityUiController::class, 'mastersMeters']);
    Route::get('/ui/masters/rates', [ParityUiController::class, 'mastersRates']);
    Route::get('/ui/inputs/mapping', [ParityUiController::class, 'inputsMapping']);
    Route::get('/ui/inputs/hr', [ParityUiController::class, 'inputsHr']);
    Route::get('/ui/inputs/readings', [ParityUiController::class, 'inputsReadings']);
    Route::get('/ui/inputs/ro', [ParityUiController::class, 'inputsRo']);
    Route::get('/ui/finalized-months', [ParityUiController::class, 'finalizedMonths']);

    Route::get('/api/dashboard/colony-kpis', [ParityUiController::class, 'colonyKpis']);
    Route::get('/api/dashboard/family-members', [ParityUiController::class, 'familyMembers']);
    Route::get('/api/dashboard/van-kids', [ParityUiController::class, 'vanKids']);
    Route::get('/ui/family-details', [ParityUiController::class, 'familyDetails']);
    Route::get('/ui/results/employee-wise', [ParityUiController::class, 'resultsEmployeeWise']);
    Route::get('/ui/results/unit-wise', [ParityUiController::class, 'resultsUnitWise']);
    Route::get('/ui/logs', [ParityUiController::class, 'logs']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN'])->group(function () {
    Route::get('/ui/admin/users', [AdminUsersController::class, 'index']);
    Route::post('/api/admin/users/create', [AdminUsersController::class, 'create']);
    Route::post('/api/admin/users/update', [AdminUsersController::class, 'update']);
    Route::post('/api/admin/users/reset-password', [AdminUsersController::class, 'resetPassword']);
    Route::get('/api/logs', [FamilyRegistryResultsController::class, 'logs']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::get('/ui/billing', [ParityUiController::class, 'billing']);
    Route::get('/ui/month-cycle', [ParityUiController::class, 'monthCycle']);
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
    Route::post('/api/electric-v1/run', [BillingDraftController::class, 'electricV1Run']);
    Route::post('/billing/fingerprint', [BillingDraftController::class, 'fingerprint']);
    Route::get('/billing/fingerprint', [BillingDraftController::class, 'fingerprint']);
    Route::get('/billing/adjustments/list', [BillingDraftController::class, 'adjustmentsList']);
    Route::get('/billing/print/{month_cycle}/{employee_id}', [BillingDraftController::class, 'printEmployee']);
    Route::get('/billing/print/<month_cycle>/<employee_id>', [BillingDraftController::class, 'printEmployeeLiteral']);
    Route::post('/billing/lock', [BillingDraftController::class, 'lock']);
    Route::post('/billing/approve', [BillingDraftController::class, 'approve']);

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
    Route::get('/units/resolve/<unit_id>', [UnitReferenceParityController::class, 'resolve']);
    Route::get('/api/units/reference', [UnitReferenceParityController::class, 'index']);
    Route::get('/api/units/reference/{unit_id}', [UnitReferenceParityController::class, 'show']);
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
    Route::get('/employees/<company_id>', [EmployeesMeterParityController::class, 'employeeGetCompat']);
    Route::get('/meter-reading/latest/{unitId}', [EmployeesMeterParityController::class, 'meterReadingLatest']);
    Route::get('/meter-reading/latest/{unit_id}', [EmployeesMeterParityController::class, 'meterReadingLatestCompat']);
    Route::get('/meter-reading/latest/<unit_id>', [EmployeesMeterParityController::class, 'meterReadingLatestCompat']);
    Route::get('/meter-unit', [EmployeesMeterParityController::class, 'meterUnit']);

    Route::get('/api/water/occupancy-snapshot', [BillingDraftController::class, 'waterOccupancySnapshot']);
    Route::get('/api/water/zone-adjustments', [BillingDraftController::class, 'waterZoneAdjustmentsGet']);
    Route::get('/api/water/allocation-preview', [BillingDraftController::class, 'waterAllocationPreview']);
    Route::get('/api/electric-v1/outputs', [BillingDraftController::class, 'electricV1Outputs']);
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
    Route::delete('/occupancy/{row_id}', [MasterDataDraftController::class, 'occupancyDeleteCompat']);
    Route::delete('/occupancy/<row_id>', [MasterDataDraftController::class, 'occupancyDeleteCompat']);
    Route::delete('/occupancy/<int:row_id>', [MasterDataDraftController::class, 'occupancyDeleteCompat']);
    Route::post('/api/occupancy/autofill', [MasterDataDraftController::class, 'occupancyAutofill']);
    Route::post('/api/water/zone-adjustments', [BillingDraftController::class, 'waterZoneAdjustmentsUpsert']);

    Route::post('/employees/import', [EmployeesMeterParityController::class, 'employeesImport']);
    Route::post('/employees/upsert', [EmployeesMeterParityController::class, 'employeesUpsert']);
    Route::post('/employees/add', [EmployeesMeterParityController::class, 'employeesAdd']);
    Route::patch('/employees/{companyId}', [EmployeesMeterParityController::class, 'employeePatch']);
    Route::patch('/employees/{company_id}', [EmployeesMeterParityController::class, 'employeePatchCompat']);
    Route::patch('/employees/<company_id>', [EmployeesMeterParityController::class, 'employeePatchCompat']);
    Route::delete('/employees/{companyId}', [EmployeesMeterParityController::class, 'employeeDelete']);
    Route::delete('/employees/{company_id}', [EmployeesMeterParityController::class, 'employeeDeleteCompat']);
    Route::delete('/employees/<company_id>', [EmployeesMeterParityController::class, 'employeeDeleteCompat']);

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
    Route::get('/imports/error-report/<token>', [ImportsMonthlySetupController::class, 'errorReportLiteral']);
    Route::get('/monthly-rates/config', [ImportsMonthlySetupController::class, 'monthlyRatesConfig']);
    Route::get('/monthly-rates/history', [ImportsMonthlySetupController::class, 'monthlyRatesHistory']);
    Route::get('/expenses/monthly-variable', [ImportsMonthlySetupController::class, 'monthlyVariableGet']);

    Route::get('/family/details/context', [FamilyRegistryResultsController::class, 'familyDetailsContext']);
    Route::get('/family/details', [FamilyRegistryResultsController::class, 'familyDetails']);
    Route::get('/registry/employees/{company_id}', [FamilyRegistryResultsController::class, 'registryEmployeeGet']);
    Route::get('/registry/employees/<company_id>', [FamilyRegistryResultsController::class, 'registryEmployeeGetLiteral']);
    Route::get('/api/results/employee-wise', [FamilyRegistryResultsController::class, 'resultsEmployeeWise']);
    Route::get('/api/results/unit-wise', [FamilyRegistryResultsController::class, 'resultsUnitWise']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,VIEWER'])->group(function () {
    Route::get('/reports/reconciliation', [BillingDraftController::class, 'reconciliationReport']);
    Route::get('/reports/monthly-summary', [BillingDraftController::class, 'monthlySummary']);
    Route::get('/reports/recovery', [BillingDraftController::class, 'recoveryReport']);
    Route::get('/reports/employee-bill-summary', [BillingDraftController::class, 'employeeBillSummary']);
    Route::get('/reports/van', [BillingDraftController::class, 'vanReport']);
    Route::get('/reports/elec-summary', [BillingDraftController::class, 'elecSummary']);
    Route::get('/export/excel/reconciliation', [BillingDraftController::class, 'exportExcelReconciliation']);
    Route::get('/export/excel/monthly-summary', [BillingDraftController::class, 'exportExcelMonthlySummary']);
    Route::get('/export/pdf/monthly-summary', [BillingDraftController::class, 'exportPdfMonthlySummary']);
});
