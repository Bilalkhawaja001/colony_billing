<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

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

    /**
     * Real precheck only (read-only) aligned to proven Flask boundary:
     * - reads hr_input/map_room/readings/ro_drinking by month_cycle
     * - returns status, stop, logs, rows_preview
     * - no writes
     */
    public function precheck(array $payload): array
    {
        $monthCycle = trim((string)($payload['month_cycle'] ?? ''));

        if (!preg_match('/^\d{2}-\d{4}$/', $monthCycle)) {
            return [
                'status' => 'failed',
                'stop' => true,
                'logs' => [[
                    'severity' => 'CRIT',
                    'code' => 'BAD_MONTH',
                    'message' => 'Invalid Month_Cycle format. Expected MM-YYYY',
                    'ref_json' => ['month_cycle' => $monthCycle],
                ]],
                'rows_preview' => [],
            ];
        }

        $hrRows = DB::select('SELECT month_cycle, company_id, active_days FROM hr_input WHERE month_cycle=?', [$monthCycle]);
        $mapRows = DB::select('SELECT month_cycle, unit_id, company_id FROM map_room WHERE month_cycle=?', [$monthCycle]);
        $unitRows = DB::select('SELECT month_cycle, meter_id, unit_id, meter_type, usage, amount FROM readings WHERE month_cycle=?', [$monthCycle]);
        $roRows = DB::select('SELECT month_cycle, unit_id, liters, amount FROM ro_drinking WHERE month_cycle=?', [$monthCycle]);

        $logs = [];
        $rowsPreview = [];

        // Proven checks from Flask engine: duplicate HR + numeric/non-negative active_days
        $seen = [];
        $companyToDays = [];
        foreach ($hrRows as $r) {
            $companyId = trim((string)($r->company_id ?? ''));
            $key = $monthCycle.'|'.$companyId;
            if (isset($seen[$key])) {
                $logs[] = [
                    'severity' => 'CRIT',
                    'code' => 'DUP_HR',
                    'message' => 'Duplicate HR row for CompanyID + Month_Cycle',
                    'ref_json' => ['company_id' => $companyId],
                ];
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'rows_preview' => []];
            }
            $seen[$key] = true;

            $daysRaw = $r->active_days ?? 0;
            if (!is_numeric((string)$daysRaw)) {
                $logs[] = [
                    'severity' => 'CRIT',
                    'code' => 'BAD_DAYS',
                    'message' => 'Active_Days is non-numeric',
                    'ref_json' => ['company_id' => $companyId],
                ];
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'rows_preview' => []];
            }

            $days = (float)$daysRaw;
            if ($days < 0) {
                $logs[] = [
                    'severity' => 'CRIT',
                    'code' => 'BAD_DAYS',
                    'message' => 'Active_Days is negative',
                    'ref_json' => ['company_id' => $companyId, 'active_days' => (string)$days],
                ];
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'rows_preview' => []];
            }

            $companyToDays[$companyId] = $days;
        }

        // Minimal proven preview assembly (read-only): aggregate unit amounts and map to occupants.
        $unitTotals = [];
        foreach ($unitRows as $r) {
            $u = trim((string)($r->unit_id ?? ''));
            $meterType = strtolower(trim((string)($r->meter_type ?? '')));
            $amount = (float)($r->amount ?? 0);
            if (!isset($unitTotals[$u])) {
                $unitTotals[$u] = ['water' => 0.0, 'power' => 0.0, 'drink' => 0.0];
            }
            if ($meterType === 'water') {
                $unitTotals[$u]['water'] += $amount;
            } elseif (in_array($meterType, ['power', 'electricity', 'elec'], true)) {
                $unitTotals[$u]['power'] += $amount;
            }
        }
        foreach ($roRows as $r) {
            $u = trim((string)($r->unit_id ?? ''));
            if (!isset($unitTotals[$u])) {
                $unitTotals[$u] = ['water' => 0.0, 'power' => 0.0, 'drink' => 0.0];
            }
            $unitTotals[$u]['drink'] += (float)($r->amount ?? 0);
        }

        $unitToCompanies = [];
        foreach ($mapRows as $m) {
            $u = trim((string)($m->unit_id ?? ''));
            $c = trim((string)($m->company_id ?? ''));
            if ($u === '') {
                continue;
            }
            $unitToCompanies[$u] ??= [];
            $unitToCompanies[$u][] = $c;
        }

        foreach ($unitTotals as $unitId => $totals) {
            $occupants = $unitToCompanies[$unitId] ?? [];
            if (count($occupants) === 0) {
                $logs[] = [
                    'severity' => 'WARN',
                    'code' => 'UNIT_NO_MAP',
                    'message' => 'Unit has charges but no room mapping',
                    'ref_json' => ['unit_id' => $unitId],
                ];
                continue;
            }

            // Draft-safe preview (not full finalize math): equal split fallback for >1 occupants.
            $split = 1 / max(count($occupants), 1);
            foreach ($occupants as $companyId) {
                $water = round($totals['water'] * $split, 2);
                $power = round($totals['power'] * $split, 2);
                $drink = round($totals['drink'] * $split, 2);
                $rowsPreview[] = [
                    'company_id' => $companyId,
                    'unit_id' => $unitId,
                    'water_amt' => $water,
                    'power_amt' => $power,
                    'drink_amt' => $drink,
                    'adjustment' => 0.0,
                    'total_amt' => round($water + $power + $drink, 2),
                ];
            }
        }

        return [
            'status' => 'ok',
            'stop' => false,
            'logs' => $logs,
            'rows_preview' => array_slice($rowsPreview, 0, 20),
            'parity_note' => 'Core precheck boundary is real/read-only; allocation split is draft approximation pending full evidence port.',
        ];
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
