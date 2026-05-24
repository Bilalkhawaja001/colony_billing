<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthDraftController;
use App\Http\Controllers\Billing\BillingDraftController;
use App\Http\Controllers\Billing\ImportsMonthlySetupController;
use App\Http\Controllers\Billing\MasterDataDraftController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Billing\FamilyRegistryResultsController;
use App\Http\Controllers\Billing\EmployeeProfileController;
use App\Http\Controllers\Billing\EmployeesMeterParityController;
use App\Http\Controllers\Billing\MonthlyActiveDaysController;
use App\Http\Controllers\Billing\DataGridController;
use App\Http\Controllers\Billing\HouseAllowanceCorrectionController;
use App\Http\Controllers\Ui\ParityUiController;
use App\Http\Controllers\Infra\InfraController;
use App\Http\Controllers\Billing\UnitReferenceParityController;
use App\Http\Controllers\Transport\TransportController;
use App\Http\Controllers\Facilities\FacilitiesController;

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
    Route::get('/profile', [AuthDraftController::class, 'showProfile']);
    Route::get('/ui/profile', fn () => redirect('/profile'));
    Route::post('/api/profile/change-password', [AuthDraftController::class, 'changePassword']);

    Route::get('/dashboard', [ParityUiController::class, 'dashboard']);
    Route::get('/imports-validation', [ParityUiController::class, 'imports']);
    Route::get('/reporting', [ParityUiController::class, 'reports']);
    Route::get('/people-residency', [ParityUiController::class, 'employeeMaster']);
    Route::get('/employee-profile/{companyId}', [EmployeeProfileController::class, 'show']);
    Route::post('/employee-profile/{companyId}/residence/assign', [EmployeeProfileController::class, 'assignResidence']);
    Route::post('/employee-profile/{companyId}/residence/shift', [EmployeeProfileController::class, 'shiftResidence']);
    Route::post('/employee-profile/{companyId}/residence/vacate', [EmployeeProfileController::class, 'vacateResidence']);
    Route::post('/employee-profile/{companyId}/family-members/{familyMemberId}/movement', [EmployeeProfileController::class, 'recordFamilyMovement']);
    Route::post('/employee-profile/{companyId}/family-members', [EmployeeProfileController::class, 'storeFamilyMember']);
    Route::put('/employee-profile/{companyId}/family-members/{familyMemberId}', [EmployeeProfileController::class, 'updateFamilyMember']);
    Route::get('/active-days-monthly', [MonthlyActiveDaysController::class, 'index']);
    Route::get('/unit-directory', [ParityUiController::class, 'unitMaster']);
    Route::get('/transport', function (\Illuminate\Http\Request $request) {
        $requestedMonth = trim((string) $request->query('month_cycle', ''));

        $monthCycle = $requestedMonth !== ''
            ? $requestedMonth
            : (string) (\Illuminate\Support\Facades\DB::table('util_month_cycle')
                ->where('state', 'OPEN')
                ->orderByDesc('created_at')
                ->value('month_cycle') ?? '');

        return view('ui.transport', [
            'monthCycle' => $monthCycle,
        ]);
    });

    Route::get('/facilities-management', [FacilitiesController::class, 'overview']);
    Route::get('/facilities-management/registry', [FacilitiesController::class, 'registry']);
    Route::get('/facilities-management/inspections', [FacilitiesController::class, 'inspections']);
    Route::get('/facilities-management/service-requests', [FacilitiesController::class, 'serviceRequests']);
    Route::get('/facilities-management/approval-queue', [FacilitiesController::class, 'approvalQueue']);
    Route::get('/facilities-management/work-orders', [FacilitiesController::class, 'workOrders']);
    Route::get('/facilities-management/daily-services', [FacilitiesController::class, 'dailyServices']);
    Route::get('/facilities-management/verification-closure', [FacilitiesController::class, 'verificationClosure']);
    Route::get('/facilities-management/reports', [FacilitiesController::class, 'reports']);
    // Hub: Meters & Readings (single sidebar entry)
    Route::get('/meters-readings', [ParityUiController::class, 'metersHub']);

    // Workspaces under the hub (no separate sidebar entries)
    Route::get('/meters-readings/registry', [ParityUiController::class, 'meterRegistry']);
    Route::get('/meters-readings/readings', [ParityUiController::class, 'meterReadings']);
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
    Route::get('/ui/monthly-active-days', fn () => redirect('/active-days-monthly'));
    Route::get('/ui/employees', fn () => redirect('/people-residency'));
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
    Route::get('/ui/transport', fn () => redirect('/transport'));
    Route::get('/ui/rates', fn () => redirect('/rates'));

    // Legacy shell aliases now point to canonical modules
    Route::get('/ui/masters/employees', fn () => redirect('/people-residency'));
    Route::get('/ui/masters/units', fn () => redirect('/unit-directory'));
    Route::get('/ui/masters/meters', fn () => redirect('/meters-readings'));
    Route::get('/ui/masters/rates', fn () => redirect('/rates'));
    Route::get('/ui/inputs/mapping', fn () => redirect('/housing-occupancy'));

    Route::get('/rates', [ParityUiController::class, 'rates']);

    Route::get('/api/dashboard/colony-kpis', [ParityUiController::class, 'colonyKpis']);
    Route::get('/api/dashboard/family-members', [ParityUiController::class, 'familyMembers']);
    Route::get('/api/dashboard/van-kids', [ParityUiController::class, 'vanKids']);
    Route::get('/api/transport/school-van/enrolments', [TransportController::class, 'schoolVanEnrolments']);
    Route::post('/api/transport/school-van/enrolments/add', [TransportController::class, 'schoolVanEnrolmentAdd']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/left', [TransportController::class, 'schoolVanEnrolmentLeave']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/cancel', [TransportController::class, 'schoolVanEnrolmentCancel']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/reactivate', [TransportController::class, 'schoolVanEnrolmentReactivate']);
    Route::post('/api/transport/school-van/enrolments/{enrolmentId}/restore-cancellation', [TransportController::class, 'schoolVanEnrolmentRestoreCancellation']);
    Route::get('/api/transport/summary', [TransportController::class, 'summary']);
    Route::post('/api/transport/month-cycle/upsert', [TransportController::class, 'monthCycleUpsert']);
    Route::post('/api/transport/school-van/bill/generate', [TransportController::class, 'generateSchoolVanBill']);
    // Route::get('/api/transport/export/csv', [TransportController::class, 'exportCsv']);
    // Route::get('/api/transport/child-month-usage', [TransportController::class, 'childMonthUsage']);
    // Route::post('/api/transport/child-month-usage/upsert', [TransportController::class, 'childMonthUsageUpsert']);
    Route::post('/api/transport/vehicles/upsert', [TransportController::class, 'vehicleUpsert']);
    Route::post('/api/transport/rent-entries/upsert', [TransportController::class, 'rentEntryUpsert']);
    Route::post('/api/transport/fuel-entries/upsert', [TransportController::class, 'fuelEntryUpsert']);
    Route::post('/api/transport/adjustments/upsert', [TransportController::class, 'adjustmentUpsert']);
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
    Route::post('/billing/electric/house-allowance/preview', [HouseAllowanceCorrectionController::class, 'preview']);
    Route::post('/billing/electric/house-allowance/apply', [HouseAllowanceCorrectionController::class, 'apply']);
    Route::post('/billing/water/compute', [BillingDraftController::class, 'waterCompute']);
    Route::post('/billing/run', [BillingDraftController::class, 'run']);
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
    Route::get('/employees/<company_id>', [EmployeesMeterParityController::class, 'employeeGetCompat']);
    Route::get('/meter-reading/latest/{unitId}', [EmployeesMeterParityController::class, 'meterReadingLatest']);
    Route::get('/meter-reading/latest/{unit_id>', [EmployeesMeterParityController::class, 'meterReadingLatestCompat']);
    Route::get('/meter-reading/latest/<unit_id>', [EmployeesMeterParityController::class, 'meterReadingLatestCompat']);
    Route::get('/meter-unit', [EmployeesMeterParityController::class, 'meterUnit']);

    Route::get('/api/water/occupancy-snapshot', [BillingDraftController::class, 'waterOccupancySnapshot']);
    Route::get('/api/water/zone-adjustments', [BillingDraftController::class, 'waterZoneAdjustmentsGet']);
    Route::get('/api/water/allocation-preview', [BillingDraftController::class, 'waterAllocationPreview']);
    Route::get('/active-days-monthly/template', [MonthlyActiveDaysController::class, 'template']);
    Route::get('/active-days-monthly/rows', [MonthlyActiveDaysController::class, 'rows']);
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
    Route::post('/active-days-monthly/preview', [MonthlyActiveDaysController::class, 'preview']);
    Route::post('/active-days-monthly/import', [MonthlyActiveDaysController::class, 'import']);
    Route::post('/employees/upsert', [EmployeesMeterParityController::class, 'employeesUpsert']);
    Route::post('/employees/add', [EmployeesMeterParityController::class, 'employeesAdd']);
    Route::patch('/employees/{companyId>', [EmployeesMeterParityController::class, 'employeePatch']);
    Route::patch('/employees/{company_id>', [EmployeesMeterParityController::class, 'employeePatchCompat']);
    Route::patch('/employees/<company_id>', [EmployeesMeterParityController::class, 'employeePatchCompat']);
    Route::delete('/employees/{companyId>', [EmployeesMeterParityController::class, 'employeeDelete']);
    Route::delete('/employees/{company_id>', [EmployeesMeterParityController::class, 'employeeDeleteCompat']);
    Route::delete('/employees/<company_id>', [EmployeesMeterParityController::class, 'employeeDeleteCompat']);

    Route::post('/meter-reading/upsert', [EmployeesMeterParityController::class, 'meterReadingUpsert']);
    Route::post('/meter-unit/upsert', [EmployeesMeterParityController::class, 'meterUnitUpsert']);

    Route::post('/api/rooms/cascade', [EmployeesMeterParityController::class, 'roomsCascade']);
    Route::get('/api/rooms/cascade', [EmployeesMeterParityController::class, 'roomsCascade']);

    Route::post('/facilities-management/registry/facilities', [FacilitiesController::class, 'storeFacility']);
    Route::post('/facilities-management/registry/components', [FacilitiesController::class, 'storeComponent']);
    Route::post('/facilities-management/service-requests', [FacilitiesController::class, 'storeServiceRequest']);
    Route::post('/facilities-management/service-requests/{id}/approve', [FacilitiesController::class, 'approveRequest']);
    Route::post('/facilities-management/service-requests/{id}/reject', [FacilitiesController::class, 'rejectRequest']);
    Route::post('/facilities-management/service-requests/{id}/convert-work-order', [FacilitiesController::class, 'convertRequestToWorkOrder']);
    Route::post('/facilities-management/work-orders/{id}/transition', [FacilitiesController::class, 'transitionWorkOrder']);
    Route::post('/facilities-management/work-orders/{id}/verify', [FacilitiesController::class, 'verifyWorkOrder']);
    Route::post('/facilities-management/work-orders/{id}/close', [FacilitiesController::class, 'closeWorkOrder']);

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
    Route::get('/family/members/master', [FamilyRegistryResultsController::class, 'familyMembersMaster']);
    Route::get('/family/members/registry', [FamilyRegistryResultsController::class, 'familyMembersRegistry']);
    Route::get('/registry/employees/{company_id>', [FamilyRegistryResultsController::class, 'registryEmployeeGet']);
    Route::get('/registry/employees/<company_id>', [FamilyRegistryResultsController::class, 'registryEmployeeGetLiteral']);
    Route::get('/api/results/employee-wise', [FamilyRegistryResultsController::class, 'resultsEmployeeWise']);
    Route::get('/api/results/unit-wise', [FamilyRegistryResultsController::class, 'resultsUnitWise']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY,VIEWER'])->group(function () {
    Route::get('/reports/reconciliation', [BillingDraftController::class, 'reconciliationReport']);
    Route::get('/reports/monthly-summary', [BillingDraftController::class, 'monthlySummary']);
    Route::get('/reports/recovery', [BillingDraftController::class, 'recoveryReport']);
    Route::get('/reports/employee-bill-summary', [BillingDraftController::class, 'employeeBillSummary']);
    Route::get('/reports/van', [BillingDraftController::class, 'vanReport']);
    Route::get('/reports/elec-summary', [BillingDraftController::class, 'elecSummary']);
    Route::get('/export/excel/reconciliation', [BillingDraftController::class, 'exportExcelReconciliation']);
    Route::get('/export/excel/monthly-summary', [BillingDraftController::class, 'exportExcelMonthlySummary']);
    Route::get('/export/pdf/monthly-summary', [BillingDraftController::class, 'exportPdfMonthlySummary']);
    Route::get('/billing/electric/export', [BillingDraftController::class, 'exportElectricBillExcel']);
    Route::get('/billing/electric/export-complete', [BillingDraftController::class, 'exportCompleteElectricBillExcel']);
    Route::get('/billing/electric/alerts-audit', [BillingDraftController::class, 'exportElectricAlertsAuditExcel']);
    Route::get('/billing/electric/missing-skipped-audit', [BillingDraftController::class, 'exportElectricMissingSkippedAuditExcel']);
    Route::get('/api/units/resident-groups', [DataGridController::class, 'unitResidentGroups']);
    Route::get('/api/units/resident-rooms', [DataGridController::class, 'unitResidentRooms']);
    Route::get('/api/units/residents', [DataGridController::class, 'unitResidents']);
    Route::get('/api/grids/{module}', [DataGridController::class, 'list']);
    Route::get('/meter-reading/list', [DataGridController::class, 'list'])->defaults('module', 'meter-readings');
    Route::get('/export/{module}', [DataGridController::class, 'export']);
    Route::get('/reports/employee-statement', [DataGridController::class, 'employeeStatement']);
    Route::get('/reports/employee-statement/print', [DataGridController::class, 'employeeStatementPrint']);
    Route::get('/reports/employee-statement/export', [DataGridController::class, 'employeeStatementExport']);
    Route::get('/reports/employee-statements/export-all', [DataGridController::class, 'employeeStatementsExportAll']);
});

Route::middleware(['ensure.auth', 'force.password.change', 'role:SUPER_ADMIN,BILLING_ADMIN,DATA_ENTRY'])->group(function () {
    Route::post('/api/grids/{module}/upsert', [DataGridController::class, 'upsert']);
});

require __DIR__.'/electric_v1.php';
