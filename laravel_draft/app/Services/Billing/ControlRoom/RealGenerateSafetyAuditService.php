<?php

namespace App\Services\Billing\ControlRoom;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RealGenerateSafetyAuditService
{
    public function __construct(private ReadinessService $readiness)
    {
    }

    public function audit(?string $monthCycle = null): array
    {
        $readiness = $this->readiness->summary($monthCycle);
        $stats = $readiness['stats'] ?? [];

        return [
            'status' => ($readiness['isReady'] ?? false) ? 'CONFIRMATION_GATE_READY' : 'CONFIRMATION_GATE_BLOCKED',
            'real_generate_enabled' => false,
            'reason' => 'Real generate remains locked. Phase 1E is safety audit and confirmation gate only.',
            'required_phrase' => 'I UNDERSTAND REAL GENERATE WILL WRITE BILL DATA',
            'month_cycle' => $readiness['month'] ?? null,
            'cycle_start_date' => $stats['cycle_start_date'] ?? null,
            'cycle_end_date' => $stats['cycle_end_date'] ?? null,
            'readiness' => [
                'is_ready' => (bool) ($readiness['isReady'] ?? false),
                'mode' => $readiness['mode'] ?? '-',
                'blocker_count' => count($readiness['blockers'] ?? []),
                'blockers' => $readiness['blockers'] ?? [],
            ],
            'input_counts' => [
                'active_employees' => $stats['active_employees'] ?? 0,
                'active_meters' => $stats['active_meters'] ?? 0,
                'current_readings' => $stats['current_readings'] ?? 0,
                'active_days_rows' => $stats['active_days_rows'] ?? 0,
                'room_allowance_rows' => $stats['room_allowance_rows'] ?? 0,
                'electric_rate' => $stats['electric_rate'] ?? null,
            ],
            'current_output_counts' => $this->currentOutputCounts(),
            'real_generate_code_path' => [
                'service' => 'App\\Services\\ElectricV1\\OrchestrationService::run',
                'controller_status' => 'not connected to Control Room real generate button',
                'transaction' => 'DB::transaction wraps output replace + exception append + run history append',
            ],
            'write_targets_if_later_approved' => [
                'electric_v1_output_employee_final' => 'replace cycle outputs',
                'electric_v1_output_employee_unit_drilldown' => 'replace cycle drilldown',
                'electric_v1_exceptions_or_audit' => 'append exceptions when present',
                'electric_v1_run_history_or_audit' => 'append run history',
            ],
            'phase1e_safety' => [
                'db_write_now' => 'NO',
                'migrate_now' => 'NO',
                'queue_dispatch_now' => 'NO',
                'real_generate_now' => 'NO',
                'delete_outputs_now' => 'NO',
                'insert_outputs_now' => 'NO',
                'confirmation_gate_only' => 'YES',
            ],
            'next_required_approval' => 'APPROVED - PHASE 1F REAL GENERATE CONTROLLED EXECUTION',
            'checked_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function currentOutputCounts(): array
    {
        $tables = [
            'bill_runs',
            'billing_rows',
            'electric_v1_output_employee_final',
            'electric_v1_output_employee_unit_drilldown',
        ];

        $counts = [];
        foreach ($tables as $table) {
            try {
                $counts[$table] = Schema::hasTable($table) ? DB::table($table)->count() : 'TABLE_MISSING';
            } catch (\Throwable $e) {
                $counts[$table] = 'COUNT_FAILED';
            }
        }

        return $counts;
    }
}
