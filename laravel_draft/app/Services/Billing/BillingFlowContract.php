<?php

namespace App\Services\Billing;

interface BillingFlowContract
{
    public function precheck(array $payload): array;

    public function finalize(array $payload): array;

    public function lock(array $payload): array;

    public function approve(array $payload): array;
}
