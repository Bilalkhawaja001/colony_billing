<?php

namespace App\Services\ElectricV1\Domain;

class ConsumptionEngine
{
    public static function compute(string $unitId, ?array $cycleReading, array $history): array
    {
        if (!$cycleReading) {
            return self::estimate($unitId, $history, [['code' => 'E_READ_MISSING_ROW', 'message' => 'Reading row missing for unit', 'severity' => 'ERROR', 'unit_id' => $unitId]]);
        }

        $status = strtoupper(trim((string)($cycleReading['reading_status'] ?? '')));
        if ($status === 'FAULTY') {
            return self::estimate($unitId, $history, []);
        }
        if ($status !== 'NORMAL') {
            return ['result' => null, 'issues' => [['code' => 'E_READ_STATUS_INVALID', 'message' => 'Unsupported reading status', 'severity' => 'ERROR', 'unit_id' => $unitId]]];
        }

        $prev = is_numeric($cycleReading['previous_reading'] ?? null) ? (float)$cycleReading['previous_reading'] : null;
        $curr = is_numeric($cycleReading['current_reading'] ?? null) ? (float)$cycleReading['current_reading'] : null;
        if ($prev === null || $curr === null) {
            return ['result' => null, 'issues' => [['code' => 'E_READ_NORMAL_INVALID_NUM', 'message' => 'NORMAL requires numeric readings', 'severity' => 'ERROR', 'unit_id' => $unitId]]];
        }
        if ($curr < $prev) {
            return ['result' => null, 'issues' => [['code' => 'E_READ_REVERSE', 'message' => 'CurrentReading is less than PreviousReading', 'severity' => 'ERROR', 'unit_id' => $unitId]]];
        }

        return ['result' => ['unit_id' => $unitId, 'gross_units' => round($curr - $prev, 4), 'is_estimated' => false, 'estimated_from_valid_cycle_count' => 0], 'issues' => []];
    }

    private static function estimate(string $unitId, array $history, array $prefixIssues): array
    {
        $valid = [];
        $sources = [];
        foreach ($history as $r) {
            $status = strtoupper(trim((string)($r['reading_status'] ?? '')));
            if ($status !== 'NORMAL') continue;
            $prev = is_numeric($r['previous_reading'] ?? null) ? (float)$r['previous_reading'] : null;
            $curr = is_numeric($r['current_reading'] ?? null) ? (float)$r['current_reading'] : null;
            if ($prev === null || $curr === null || $curr < $prev) continue;
            $valid[] = $curr - $prev;
            $sources[] = ($r['cycle_start_date'] ?? '').'..'.($r['cycle_end_date'] ?? '');
            if (count($valid) >= 3) break;
        }

        if (count($valid) === 0) {
            $issues = $prefixIssues;
            $issues[] = ['code' => 'E_READ_ESTIMATE_INSUFF', 'message' => 'No prior valid cycles available for estimation', 'severity' => 'ERROR', 'unit_id' => $unitId];
            return ['result' => null, 'issues' => $issues];
        }

        $est = round(array_sum($valid) / count($valid), 4);
        $issues = $prefixIssues;
        $issues[] = ['code' => 'W_READ_ESTIMATED', 'message' => 'Estimated from previous valid cycles', 'severity' => 'WARNING', 'unit_id' => $unitId];

        return ['result' => ['unit_id' => $unitId, 'gross_units' => $est, 'is_estimated' => true, 'estimate_source_cycle1' => $sources[0] ?? null, 'estimate_source_cycle2' => $sources[1] ?? null, 'estimate_source_cycle3' => $sources[2] ?? null, 'estimated_from_valid_cycle_count' => count($valid)], 'issues' => $issues];
    }
}
