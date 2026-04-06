<?php

namespace App\Services\Billing;

interface BillingFlowContract
{
    public function ratesUpsert(array $payload): array;

    public function ratesApprove(array $payload): array;

    public function run(array $payload): array;

    public function precheck(array $payload): array;

    public function finalize(array $payload): array;

    public function lock(array $payload): array;

    public function approve(array $payload): array;

    public function adjustmentCreate(array $payload): array;

    public function adjustmentApprove(array $payload): array;

    public function recoveryPayment(array $payload): array;

    public function reconciliationReport(array $payload): array;

    public function monthlySummary(array $payload): array;

    public function recoveryReport(array $payload): array;

    public function employeeBillSummary(array $payload): array;

    public function vanReport(array $payload): array;

    public function elecSummary(array $payload): array;

    public function elecCompute(array $payload): array;

    public function waterCompute(array $payload): array;

    public function waterOccupancySnapshot(array $payload): array;

    public function waterZoneAdjustmentsGet(array $payload): array;

    public function waterZoneAdjustmentsUpsert(array $payload): array;

    public function waterAllocationPreview(array $payload): array;

    public function fingerprint(array $payload): array;

    public function adjustmentsList(array $payload): array;

    public function printEmployee(string $monthCycle, string $employeeId): array;

    public function exportExcelReconciliation(array $payload): array;

    public function exportExcelMonthlySummary(array $payload): array;

    public function exportPdfMonthlySummary(array $payload): array;
}
