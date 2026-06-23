<?php

namespace App\Services\Billing\ControlRoom;

use Illuminate\Support\Str;

class GenerateDryRunService
{
    public function __construct(private ReadinessService $readiness)
    {
    }

    public function run(?string $monthCycle = null): array
    {
        $readiness = $this->readiness->summary($monthCycle);
        $stats = $readiness['stats'] ?? [];

        $dryRunId = 'DRY-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(6));

        return [
            'dry_run_id' => $dryRunId,
            'status' => ($readiness['isReady'] ?? false) ? 'DRY_RUN_READY' : 'DRY_RUN_BLOCKED',
            'message' => ($readiness['isReady'] ?? false)
                ? 'Generate gate passed. No bill rows were written. Real generate still requires Phase 1E approval.'
                : 'Generate gate blocked by readiness issues. No bill rows were written.',
            'month_cycle' => $readiness['month'] ?? null,
            'cycle_start_date' => $stats['cycle_start_date'] ?? null,
            'cycle_end_date' => $stats['cycle_end_date'] ?? null,
            'cycle_days' => $stats['cycle_days'] ?? null,
            'active_employees' => $stats['active_employees'] ?? 0,
            'active_meters' => $stats['active_meters'] ?? 0,
            'current_readings' => $stats['current_readings'] ?? 0,
            'active_days_rows' => $stats['active_days_rows'] ?? 0,
            'electric_rate' => $stats['electric_rate'] ?? null,
            'blocker_count' => count($readiness['blockers'] ?? []),
            'blockers' => $readiness['blockers'] ?? [],
            'safety' => [
                'db_write' => 'NO',
                'migrate' => 'NO',
                'queue_dispatch' => 'NO',
                'bill_run_insert' => 'NO',
                'billing_rows_insert' => 'NO',
                'electric_output_delete' => 'NO',
                'electric_output_insert' => 'NO',
            ],
            'checked_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
