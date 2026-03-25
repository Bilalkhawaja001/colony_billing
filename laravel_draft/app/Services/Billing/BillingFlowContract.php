<?php

namespace App\Services\Billing;

interface BillingFlowContract
{
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

    public function exportExcelReconciliation(array $payload): array;
}
