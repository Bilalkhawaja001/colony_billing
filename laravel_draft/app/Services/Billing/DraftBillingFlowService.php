<?php

namespace App\Services\Billing;

class DraftBillingFlowService implements BillingFlowContract
{
    private function blocked(string $action, array $payload = []): array
    {
        return [
            'status' => 'blocked',
            'phase' => 'LIMITED_GO',
            'action' => $action,
            'message' => 'blocked by migration phase',
            'implemented' => false,
            'payload_echo' => $payload,
        ];
    }

    public function precheck(array $payload): array
    {
        return $this->blocked('billing.precheck', $payload);
    }

    public function finalize(array $payload): array
    {
        return $this->blocked('billing.finalize', $payload);
    }

    public function lock(array $payload): array
    {
        return $this->blocked('billing.lock', $payload);
    }

    public function approve(array $payload): array
    {
        return $this->blocked('billing.approve', $payload);
    }
}
