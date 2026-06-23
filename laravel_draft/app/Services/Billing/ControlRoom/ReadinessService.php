<?php

namespace App\Services\Billing\ControlRoom;

class ReadinessService
{
    public function summary(): array
    {
        return [
            'isReady' => false,
            'mode' => 'PHASE_1A_SCAFFOLD_ONLY',
            'month' => null,
            'lastChecked' => now()->format('Y-m-d H:i:s'),
            'stats' => [
                'employees' => 'TODO real data',
                'meters' => 'TODO real data',
                'readings' => 'TODO real data',
                'bill_runs' => 'TODO real data',
            ],
            'blockers' => [
                [
                    'code' => 'SCAFFOLD_ONLY',
                    'title' => 'Real readiness logic pending',
                    'message' => 'Generate locked. Phase 1A scaffold only; no DB write and no real generation.',
                    'severity' => 'warning',
                ],
            ],
            'warnings' => [],
        ];
    }
}
