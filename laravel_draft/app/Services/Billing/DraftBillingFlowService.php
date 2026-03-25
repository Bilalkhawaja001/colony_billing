<?php

namespace App\Services\Billing;

use Illuminate\Database\QueryException;
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

    private function monthValid(string $monthCycle): bool
    {
        return (bool) preg_match('/^\d{2}-\d{4}$/', $monthCycle);
    }

    private function loadInputs(string $monthCycle): array
    {
        return [
            'hr_rows' => DB::select('SELECT month_cycle, company_id, active_days FROM hr_input WHERE month_cycle=?', [$monthCycle]),
            'map_rows' => DB::select('SELECT month_cycle, unit_id, company_id FROM map_room WHERE month_cycle=?', [$monthCycle]),
            'unit_rows' => DB::select('SELECT month_cycle, meter_id, unit_id, meter_type, usage, amount FROM readings WHERE month_cycle=?', [$monthCycle]),
            'ro_rows' => DB::select('SELECT month_cycle, unit_id, liters, amount FROM ro_drinking WHERE month_cycle=?', [$monthCycle]),
        ];
    }

    private function duplicateHrRows(string $monthCycle): array
    {
        return DB::select(
            'SELECT company_id, COUNT(*) AS c FROM hr_input WHERE month_cycle=? GROUP BY company_id HAVING COUNT(*) > 1',
            [$monthCycle]
        );
    }

    private function deterministicFingerprint(string $monthCycle, array $billingRows): string
    {
        $lines = [];
        foreach ($billingRows as $r) {
            $lines[] = implode('|', [
                $monthCycle,
                (string)($r['company_id'] ?? ''),
                'TOTAL',
                '1',
                '1',
                number_format((float)($r['total_amt'] ?? 0), 2, '.', ''),
                (string)($r['unit_id'] ?? ''),
            ]);
        }
        sort($lines);
        return hash('sha256', implode("\n", $lines));
    }

    private function draftEngine(string $monthCycle, array $inputs): array
    {
        $hrRows = $inputs['hr_rows'];
        $mapRows = $inputs['map_rows'];
        $unitRows = $inputs['unit_rows'];
        $roRows = $inputs['ro_rows'];

        $logs = [];
        $rowsPreview = [];

        $seen = [];
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
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'billing_rows' => [], 'rows_preview' => []];
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
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'billing_rows' => [], 'rows_preview' => []];
            }

            if ((float)$daysRaw < 0) {
                $logs[] = [
                    'severity' => 'CRIT',
                    'code' => 'BAD_DAYS',
                    'message' => 'Active_Days is negative',
                    'ref_json' => ['company_id' => $companyId, 'active_days' => (string)$daysRaw],
                ];
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'billing_rows' => [], 'rows_preview' => []];
            }
        }

        $unitTotals = [];
        foreach ($unitRows as $r) {
            $u = trim((string)($r->unit_id ?? ''));
            $meterType = strtolower(trim((string)($r->meter_type ?? '')));
            $amount = (float)($r->amount ?? 0);
            $unitTotals[$u] ??= ['water' => 0.0, 'power' => 0.0, 'drink' => 0.0];
            if ($meterType === 'water') {
                $unitTotals[$u]['water'] += $amount;
            } elseif (in_array($meterType, ['power', 'electricity', 'elec'], true)) {
                $unitTotals[$u]['power'] += $amount;
            }
        }

        foreach ($roRows as $r) {
            $u = trim((string)($r->unit_id ?? ''));
            $unitTotals[$u] ??= ['water' => 0.0, 'power' => 0.0, 'drink' => 0.0];
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

            $split = 1 / max(count($occupants), 1);
            foreach ($occupants as $companyId) {
                $water = round($totals['water'] * $split, 2);
                $power = round($totals['power'] * $split, 2);
                $drink = round($totals['drink'] * $split, 2);
                $row = [
                    'company_id' => $companyId,
                    'unit_id' => $unitId,
                    'water_amt' => $water,
                    'power_amt' => $power,
                    'drink_amt' => $drink,
                    'adjustment' => 0.0,
                    'total_amt' => round($water + $power + $drink, 2),
                ];
                $rowsPreview[] = $row;
            }
        }

        return [
            'status' => 'ok',
            'stop' => false,
            'logs' => $logs,
            'billing_rows' => $rowsPreview,
            'rows_preview' => $rowsPreview,
        ];
    }

    /**
     * Real precheck only (read-only) aligned to proven Flask boundary.
     */
    public function precheck(array $payload): array
    {
        $monthCycle = trim((string)($payload['month_cycle'] ?? ''));

        if (!$this->monthValid($monthCycle)) {
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

        $inputs = $this->loadInputs($monthCycle);
        $out = $this->draftEngine($monthCycle, $inputs);

        return [
            'status' => $out['status'] ?? 'failed',
            'stop' => (bool)($out['stop'] ?? true),
            'logs' => $out['logs'] ?? [],
            'rows_preview' => array_slice($out['rows_preview'] ?? [], 0, 20),
            'parity_note' => 'Core precheck boundary is real/read-only; allocation split is draft approximation pending full evidence port.',
        ];
    }

    /**
     * Real finalize boundary from proven evidence:
     * - explicit txn
     * - same-month delete-and-replace
     * - duplicate-HR fail-fast with failed run/logs
     * - rerun idempotent for targeted output tables
     */
    public function finalize(array $payload): array
    {
        $monthCycle = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($monthCycle)) {
            return [
                '_http' => 400,
                'status' => 'failed',
                'error' => 'Invalid Month_Cycle format. Expected MM-YYYY',
            ];
        }

        $dupHr = $this->duplicateHrRows($monthCycle);
        if (!empty($dupHr)) {
            $runId = substr(bin2hex(random_bytes(8)), 0, 12);

            DB::beginTransaction();
            try {
                DB::delete('DELETE FROM billing_rows WHERE month_cycle=?', [$monthCycle]);
                DB::delete('DELETE FROM logs WHERE month_cycle=?', [$monthCycle]);
                DB::delete('DELETE FROM billing_run WHERE month_cycle=?', [$monthCycle]);

                DB::insert('INSERT INTO billing_run(run_id,month_cycle,status,started_at) VALUES(?,?,?,CURRENT_TIMESTAMP)', [$runId, $monthCycle, 'failed']);
                foreach ($dupHr as $r) {
                    DB::insert(
                        'INSERT INTO logs(run_id,month_cycle,severity,code,message,ref_json) VALUES(?,?,?,?,?,?)',
                        [$runId, $monthCycle, 'CRIT', 'DUP_HR', 'Duplicate HR entry for company/month', json_encode(['month_cycle' => $monthCycle, 'company_id' => $r->company_id ?? null])]
                    );
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            return [
                '_http' => 409,
                'status' => 'failed',
                'run_id' => $runId,
                'error' => 'Duplicate HR entry for company/month',
            ];
        }

        $inputs = $this->loadInputs($monthCycle);
        $out = $this->draftEngine($monthCycle, $inputs);
        $runId = substr(bin2hex(random_bytes(8)), 0, 12);

        DB::beginTransaction();
        try {
            // Proven idempotent replace for same month
            DB::delete('DELETE FROM billing_rows WHERE month_cycle=?', [$monthCycle]);
            DB::delete('DELETE FROM logs WHERE month_cycle=?', [$monthCycle]);
            DB::delete('DELETE FROM billing_run WHERE month_cycle=?', [$monthCycle]);

            if (!empty($out['stop'])) {
                DB::insert('INSERT INTO billing_run(run_id,month_cycle,status,started_at) VALUES(?,?,?,CURRENT_TIMESTAMP)', [$runId, $monthCycle, 'failed']);
                foreach (($out['logs'] ?? []) as $lg) {
                    DB::insert(
                        'INSERT INTO logs(run_id,month_cycle,severity,code,message,ref_json) VALUES(?,?,?,?,?,?)',
                        [$runId, $monthCycle, (string)$lg['severity'], (string)$lg['code'], (string)$lg['message'], json_encode($lg['ref_json'] ?? new \stdClass())]
                    );
                }
                DB::commit();
                return ['status' => 'failed', 'run_id' => $runId, 'parity_note' => 'Stopped by proven precheck/finalize guard'];
            }

            $billingRows = $out['billing_rows'] ?? [];
            $fp = $this->deterministicFingerprint($monthCycle, $billingRows);

            DB::insert(
                'INSERT INTO billing_run(run_id,month_cycle,status,started_at,finalized_at,fingerprint) VALUES(?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,?)',
                [$runId, $monthCycle, 'final', $fp]
            );

            foreach ($billingRows as $r) {
                DB::insert(
                    'INSERT INTO billing_rows(run_id,month_cycle,company_id,unit_id,water_amt,power_amt,drink_amt,adjustment,total_amt,rounded_2dp) VALUES(?,?,?,?,?,?,?,?,?,?)',
                    [
                        $runId,
                        $monthCycle,
                        (string)$r['company_id'],
                        (string)$r['unit_id'],
                        (float)$r['water_amt'],
                        (float)$r['power_amt'],
                        (float)$r['drink_amt'],
                        (float)$r['adjustment'],
                        (float)$r['total_amt'],
                        (float)$r['total_amt'],
                    ]
                );
            }

            foreach (($out['logs'] ?? []) as $lg) {
                DB::insert(
                    'INSERT INTO logs(run_id,month_cycle,severity,code,message,ref_json) VALUES(?,?,?,?,?,?)',
                    [$runId, $monthCycle, (string)$lg['severity'], (string)$lg['code'], (string)$lg['message'], json_encode($lg['ref_json'] ?? new \stdClass())]
                );
            }

            DB::statement('INSERT OR REPLACE INTO finalized_months(month_cycle,finalized_at) VALUES(?,CURRENT_TIMESTAMP)', [$monthCycle]);

            DB::commit();

            return [
                'status' => 'ok',
                'run_id' => $runId,
                'rows' => count($billingRows),
                'parity_note' => 'Finalize boundary is real with proven transaction + same-month replace semantics; computation internals still draft approximation.',
            ];
        } catch (QueryException $e) {
            DB::rollBack();
            $msg = strtolower((string)$e->getMessage());
            if (str_contains($msg, 'hr_input') && str_contains($msg, 'month_cycle') && str_contains($msg, 'company_id')) {
                $runId2 = substr(bin2hex(random_bytes(8)), 0, 12);
                DB::beginTransaction();
                try {
                    DB::delete('DELETE FROM logs WHERE month_cycle=?', [$monthCycle]);
                    DB::delete('DELETE FROM billing_run WHERE month_cycle=?', [$monthCycle]);
                    DB::insert('INSERT INTO billing_run(run_id,month_cycle,status,started_at) VALUES(?,?,?,CURRENT_TIMESTAMP)', [$runId2, $monthCycle, 'failed']);
                    DB::insert(
                        'INSERT INTO logs(run_id,month_cycle,severity,code,message,ref_json) VALUES(?,?,?,?,?,?)',
                        [$runId2, $monthCycle, 'CRIT', 'DUP_HR', 'Duplicate HR entry for company/month', json_encode(['month_cycle' => $monthCycle])]
                    );
                    DB::commit();
                } catch (\Throwable $e2) {
                    DB::rollBack();
                    throw $e2;
                }

                return [
                    '_http' => 409,
                    'status' => 'failed',
                    'run_id' => $runId2,
                    'error' => 'Duplicate HR entry for company/month',
                    'parity_note' => 'Integrity fallback path executed from proven Flask branch',
                ];
            }
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function lock(array $payload): array
    {
        $runId = (int)($payload['run_id'] ?? 0);
        if ($runId <= 0) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'run_id is required'];
        }

        $run = DB::selectOne('SELECT id, month_cycle, run_status FROM util_billing_run WHERE id=?', [$runId]);
        if (!$run) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'billing_run not found'];
        }

        if (($run->run_status ?? null) !== 'APPROVED') {
            return ['_http' => 409, 'status' => 'error', 'error' => 'Invalid billing_run transition from '.($run->run_status ?? 'UNKNOWN')];
        }

        $month = DB::selectOne('SELECT state FROM util_month_cycle WHERE month_cycle=?', [$run->month_cycle]);
        if (!$month) {
            return ['_http' => 409, 'status' => 'error', 'error' => 'month_cycle not found for billing_run'];
        }

        if (($month->state ?? null) !== 'APPROVAL') {
            return ['_http' => 409, 'status' => 'error', 'error' => 'Month must be in APPROVAL before lock'];
        }

        DB::update("UPDATE util_billing_run SET run_status='LOCKED' WHERE id=? AND run_status='APPROVED'", [$runId]);

        return ['status' => 'ok', 'run_id' => $runId, 'run_status' => 'LOCKED'];
    }

    public function approve(array $payload): array
    {
        return [
            '_http' => 410,
            'status' => 'error',
            'error' => 'approval flow removed; use direct finalize flow',
            'parity_note' => 'Flask evidence keeps /billing/approve intentionally removed',
        ];
    }
}
