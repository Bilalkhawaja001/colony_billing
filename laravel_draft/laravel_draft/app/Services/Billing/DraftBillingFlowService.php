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

    public function ratesUpsert(array $payload): array
    {
        $monthCycle = $this->normalizeMonthCycle((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($monthCycle)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle must be MM-YYYY'];
        }

        DB::statement(
            'INSERT INTO util_rate_monthly(month_cycle,elec_rate,water_general_rate,water_drinking_rate,school_van_rate)
             VALUES(?,?,?,?,?)
             ON CONFLICT(month_cycle) DO UPDATE SET
             elec_rate=excluded.elec_rate,
             water_general_rate=excluded.water_general_rate,
             water_drinking_rate=excluded.water_drinking_rate,
             school_van_rate=excluded.school_van_rate',
            [
                $monthCycle,
                (float)($payload['elec_rate'] ?? 0),
                (float)($payload['water_general_rate'] ?? 0),
                (float)($payload['water_drinking_rate'] ?? 0),
                (float)($payload['school_van_rate'] ?? 0),
            ]
        );

        return ['status' => 'ok', 'month_cycle' => $monthCycle];
    }

    public function ratesApprove(array $payload): array
    {
        $monthCycle = $this->normalizeMonthCycle((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($monthCycle)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle must be MM-YYYY'];
        }

        $updated = DB::update(
            'UPDATE util_rate_monthly SET approved_by_user_id=?, approved_at=CURRENT_TIMESTAMP WHERE month_cycle=?',
            [
                (int)($payload['actor_user_id'] ?? session('actor_user_id') ?? session('user_id') ?? 1),
                $monthCycle,
            ]
        );

        if ($updated < 1) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'rates not found for month_cycle'];
        }

        return ['status' => 'ok', 'month_cycle' => $monthCycle];
    }

    private function normalizeMonthCycle(string $monthCycle): string
    {
        $monthCycle = trim($monthCycle);
        if (preg_match('/^\d{4}-\d{2}$/', $monthCycle) === 1) {
            return substr($monthCycle, 5, 2).'-'.substr($monthCycle, 0, 4);
        }

        return $monthCycle;
    }

    private function monthValid(string $monthCycle): bool
    {
        return (bool) preg_match('/^\d{2}-\d{4}$/', $monthCycle);
    }

    private function monthState(string $monthCycle): ?string
    {
        $row = DB::selectOne('SELECT state FROM util_month_cycle WHERE month_cycle=?', [$monthCycle]);
        return $row ? strtoupper((string)$row->state) : null;
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

    private function lastInsertId(): int
    {
        $row = DB::selectOne('SELECT last_insert_rowid() AS id');
        return (int)($row->id ?? 0);
    }

    private function deterministicFingerprint(string $monthCycle, array $billingRows): string
    {
        $lines = [];
        foreach ($billingRows as $r) {
            $lines[] = implode('|', [
                $monthCycle,
                (string)($r['company_id'] ?? ''),
                'TOTAL',
                number_format((float)($r['water_amt'] ?? 0), 2, '.', ''),
                number_format((float)($r['power_amt'] ?? 0), 2, '.', ''),
                number_format((float)($r['drink_amt'] ?? 0), 2, '.', ''),
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
        $billingRows = [];

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
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'unit_totals' => [], 'billing_rows' => []];
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
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'unit_totals' => [], 'billing_rows' => []];
            }

            $d = (float)$daysRaw;
            if ($d < 0) {
                $logs[] = [
                    'severity' => 'CRIT',
                    'code' => 'BAD_DAYS',
                    'message' => 'Active_Days is negative',
                    'ref_json' => ['company_id' => $companyId, 'active_days' => (string)$daysRaw],
                ];
                return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'unit_totals' => [], 'billing_rows' => []];
            }

            $companyToDays[$companyId] = $d;
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

        $hrCompanies = array_keys($companyToDays);
        $mappedCompanies = [];
        foreach ($unitToCompanies as $arr) {
            foreach ($arr as $c) {
                $mappedCompanies[$c] = true;
            }
        }
        foreach ($hrCompanies as $c) {
            if (!isset($mappedCompanies[$c])) {
                $logs[] = [
                    'severity' => 'INFO',
                    'code' => 'HR_UNUSED',
                    'message' => 'HR row not used because no room mapping',
                    'ref_json' => ['company_id' => $c],
                ];
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

        $reconUnitTotals = [];
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

            $reconUnitTotals[$unitId] = $totals;

            $dayMap = [];
            foreach ($occupants as $c) {
                if (array_key_exists($c, $companyToDays)) {
                    $dayMap[$c] = (float)$companyToDays[$c];
                } else {
                    $dayMap[$c] = 30.0;
                    $logs[] = [
                        'severity' => 'WARN',
                        'code' => 'GHOST_TENANT_PENALTY',
                        'message' => 'Mapped employee missing in HR; penalty days=30 applied',
                        'ref_json' => ['unit_id' => $unitId, 'company_id' => $c],
                    ];
                }
            }

            $unitTotalDays = array_sum($dayMap);

            if (count($occupants) === 1) {
                $c = $occupants[0];
                $billingRows[] = [
                    'company_id' => $c,
                    'unit_id' => $unitId,
                    'water_amt' => round($totals['water'], 2),
                    'power_amt' => round($totals['power'], 2),
                    'drink_amt' => round($totals['drink'], 2),
                    'adjustment' => 0.00,
                    'total_amt' => round($totals['water'] + $totals['power'] + $totals['drink'], 2),
                ];
                continue;
            }

            if ($unitTotalDays <= 0) {
                $logs[] = [
                    'severity' => 'CRIT',
                    'code' => 'UNIT_ZERO_DAYS',
                    'message' => 'Multi-occupant unit has zero total days; no bill generated',
                    'ref_json' => ['unit_id' => $unitId],
                ];
                continue;
            }

            foreach ($occupants as $c) {
                $ratio = ((float)$dayMap[$c]) / $unitTotalDays;
                $water = round($totals['water'] * $ratio, 2);
                $power = round($totals['power'] * $ratio, 2);
                $drink = round($totals['drink'] * $ratio, 2);
                $billingRows[] = [
                    'company_id' => $c,
                    'unit_id' => $unitId,
                    'water_amt' => $water,
                    'power_amt' => $power,
                    'drink_amt' => $drink,
                    'adjustment' => 0.00,
                    'total_amt' => round($water + $power + $drink, 2),
                ];
            }

            // deterministic remainder correction per utility
            $idx = [];
            foreach ($billingRows as $k => $br) {
                if (($br['unit_id'] ?? '') === $unitId) {
                    $idx[] = $k;
                }
            }
            if (!empty($idx)) {
                $lastK = end($idx);
                foreach (['water_amt' => $totals['water'], 'power_amt' => $totals['power'], 'drink_amt' => $totals['drink']] as $k => $total) {
                    $cur = 0.0;
                    foreach ($idx as $j) {
                        $cur += (float)$billingRows[$j][$k];
                    }
                    $diff = round(((float)$total - $cur), 2);
                    if (abs($diff) > 0.0001) {
                        $billingRows[$lastK][$k] = round(((float)$billingRows[$lastK][$k]) + $diff, 2);
                    }
                }
                $billingRows[$lastK]['total_amt'] = round(
                    (float)$billingRows[$lastK]['water_amt'] +
                    (float)$billingRows[$lastK]['power_amt'] +
                    (float)$billingRows[$lastK]['drink_amt'] +
                    (float)$billingRows[$lastK]['adjustment'],
                    2
                );
            }
        }

        $sumEmp = 0.0;
        foreach ($billingRows as $r) {
            $sumEmp += (float)$r['total_amt'];
        }
        $sumEmp = round($sumEmp, 2);

        $sumUnits = 0.0;
        foreach ($reconUnitTotals as $t) {
            $sumUnits += (float)$t['water'] + (float)$t['power'] + (float)$t['drink'];
        }
        $sumUnits = round($sumUnits, 2);

        if (abs($sumEmp - $sumUnits) > 0.01) {
            $logs[] = [
                'severity' => 'CRIT',
                'code' => 'RECON_FAIL',
                'message' => 'Employee totals and unit totals mismatch',
                'ref_json' => ['employee_total' => (string)$sumEmp, 'unit_total' => (string)$sumUnits],
            ];
            return ['status' => 'failed', 'stop' => true, 'logs' => $logs, 'unit_totals' => $unitTotals, 'billing_rows' => $billingRows];
        }

        return [
            'status' => 'ok',
            'stop' => false,
            'logs' => $logs,
            'unit_totals' => $unitTotals,
            'billing_rows' => $billingRows,
            'rows_preview' => $billingRows,
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
            'parity_note' => 'Precheck logic aligned to evidenced engine phases (validation, allocation, reconciliation guard).',
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
        $monthCycle = $this->normalizeMonthCycle((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($monthCycle)) {
            return [
                '_http' => 400,
                'status' => 'failed',
                'error' => 'Invalid Month_Cycle format. Expected MM-YYYY',
            ];
        }

        $dupHr = $this->duplicateHrRows($monthCycle);
        if (!empty($dupHr)) {

            DB::beginTransaction();
            try {
                DB::delete('DELETE FROM util_billing_line WHERE month_cycle=?', [$monthCycle]);
                DB::delete('DELETE FROM util_billing_run WHERE month_cycle=?', [$monthCycle]);

                DB::insert('INSERT INTO util_billing_run(month_cycle, run_key, run_status) VALUES(?,?,?)', [$monthCycle, 'run_'.substr(bin2hex(random_bytes(8)),0,12), 'FAILED']);
                $failRunId = $this->lastInsertId();
                foreach ($dupHr as $r) {
                    DB::insert(
                        'INSERT INTO util_audit_log(entity_type, entity_id, action, actor_user_id, before_json, after_json, correlation_id) VALUES(?,?,?,?,?,?,?)',
                        ['billing_run', (string)$failRunId, 'DUP_HR', (string)(session('actor_user_id') ?? session('user_id') ?? '0'), null, json_encode(['month_cycle' => $monthCycle, 'company_id' => $r->company_id ?? null]), null]
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
                'run_id' => $failRunId ?? null,
                'error' => 'Duplicate HR entry for company/month',
            ];
        }

        $inputs = $this->loadInputs($monthCycle);
        $out = $this->draftEngine($monthCycle, $inputs);

        DB::beginTransaction();
        try {
            // Proven idempotent replace for same month
            DB::delete('DELETE FROM util_billing_line WHERE month_cycle=?', [$monthCycle]);
            DB::delete('DELETE FROM util_billing_run WHERE month_cycle=?', [$monthCycle]);

            if (!empty($out['stop'])) {
                DB::insert('INSERT INTO util_billing_run(month_cycle, run_key, run_status) VALUES(?,?,?)', [$monthCycle, 'run_'.substr(bin2hex(random_bytes(8)),0,12), 'FAILED']);
                $failedRunId = $this->lastInsertId();
                foreach (($out['logs'] ?? []) as $lg) {
                    DB::insert(
                        'INSERT INTO util_audit_log(entity_type, entity_id, action, actor_user_id, before_json, after_json, correlation_id) VALUES(?,?,?,?,?,?,?)',
                        ['billing_run', (string)$failedRunId, (string)$lg['code'], (string)(session('actor_user_id') ?? session('user_id') ?? '0'), null, json_encode(['severity'=>$lg['severity'],'message'=>$lg['message'],'ref_json'=>$lg['ref_json'] ?? new \stdClass()]), null]
                    );
                }
                DB::commit();
                return ['status' => 'failed', 'run_id' => $failedRunId, 'parity_note' => 'Stopped by proven precheck/finalize guard'];
            }

            $billingRows = $out['billing_rows'] ?? [];
            $fp = $this->deterministicFingerprint($monthCycle, $billingRows);

            DB::insert(
                'INSERT INTO util_billing_run(month_cycle, run_key, run_status) VALUES(?,?,?)',
                [$monthCycle, 'fp_'.$fp, 'APPROVED']
            );
            $newRunId = $this->lastInsertId();

            foreach ($billingRows as $r) {
                $employeeId = (string)$r['company_id'];
                $unitId = (string)$r['unit_id'];
                $water = (float)$r['water_amt'];
                $power = (float)$r['power_amt'];
                $drink = (float)$r['drink_amt'];

                if (abs($water) > 0.0001) {
                    DB::insert(
                        'INSERT INTO util_billing_line(billing_run_id, month_cycle, employee_id, utility_type, qty, rate, amount, source_ref) VALUES(?,?,?,?,?,?,?,?)',
                        [$newRunId, $monthCycle, $employeeId, 'WATER', 1, 0, $water, $unitId]
                    );
                }
                if (abs($power) > 0.0001) {
                    DB::insert(
                        'INSERT INTO util_billing_line(billing_run_id, month_cycle, employee_id, utility_type, qty, rate, amount, source_ref) VALUES(?,?,?,?,?,?,?,?)',
                        [$newRunId, $monthCycle, $employeeId, 'ELEC', 1, 0, $power, $unitId]
                    );
                }
                if (abs($drink) > 0.0001) {
                    DB::insert(
                        'INSERT INTO util_billing_line(billing_run_id, month_cycle, employee_id, utility_type, qty, rate, amount, source_ref) VALUES(?,?,?,?,?,?,?,?)',
                        [$newRunId, $monthCycle, $employeeId, 'WATER_DRINKING', 1, 0, $drink, $unitId]
                    );
                }
            }

            foreach (($out['logs'] ?? []) as $lg) {
                DB::insert(
                    'INSERT INTO util_audit_log(entity_type, entity_id, action, actor_user_id, before_json, after_json, correlation_id) VALUES(?,?,?,?,?,?,?)',
                    ['billing_run', (string)$newRunId, (string)$lg['code'], (string)(session('actor_user_id') ?? session('user_id') ?? '0'), null, json_encode(['severity'=>$lg['severity'],'message'=>$lg['message'],'ref_json'=>$lg['ref_json'] ?? new \stdClass()]), null]
                );
            }

            DB::statement('INSERT OR REPLACE INTO finalized_months(month_cycle, finalized_at) VALUES(?, CURRENT_TIMESTAMP)', [$monthCycle]);

            DB::commit();

            return [
                'status' => 'ok',
                'run_id' => $newRunId,
                'rows' => count($billingRows),
                'parity_note' => 'Finalize compute aligned to evidenced run_colony_billing phases; transaction and idempotent replace are active.',
            ];
        } catch (QueryException $e) {
            DB::rollBack();
            $msg = strtolower((string)$e->getMessage());
            if (str_contains($msg, 'hr_input') && str_contains($msg, 'month_cycle') && str_contains($msg, 'company_id')) {
                $runId2 = substr(bin2hex(random_bytes(8)), 0, 12);
                DB::beginTransaction();
                try {
                    DB::delete('DELETE FROM util_billing_run WHERE month_cycle=?', [$monthCycle]);
                    DB::insert('INSERT INTO util_billing_run(month_cycle, run_key, run_status) VALUES(?,?,?)', [$monthCycle, 'run_'.substr(bin2hex(random_bytes(8)),0,12), 'FAILED']);
                    $runId2 = $this->lastInsertId();
                    DB::insert(
                        'INSERT INTO util_audit_log(entity_type, entity_id, action, actor_user_id, before_json, after_json, correlation_id) VALUES(?,?,?,?,?,?,?)',
                        ['billing_run', (string)$runId2, 'DUP_HR', (string)(session('actor_user_id') ?? session('user_id') ?? '0'), null, json_encode(['month_cycle' => $monthCycle]), null]
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
            'error' => 'billing approve flow removed; use finalize/lock workflow',
            'parity_note' => 'Flask evidence has immediate 410 for /billing/approve',
        ];
    }

    public function adjustmentCreate(array $payload): array
    {
        return [
            '_http' => 410,
            'status' => 'error',
            'error' => 'deduction/adjustment flow removed; billing generation only',
            'parity_note' => 'Flask evidence has immediate 410 for /billing/adjustments/create',
        ];
    }

    public function adjustmentApprove(array $payload): array
    {
        return [
            '_http' => 410,
            'status' => 'error',
            'error' => 'adjustment approvals removed',
            'parity_note' => 'Flask evidence has immediate 410 for /billing/adjustments/approve',
        ];
    }

    public function recoveryPayment(array $payload): array
    {
        return [
            '_http' => 410,
            'status' => 'error',
            'error' => 'payment receiving disabled; billing generation only',
            'parity_note' => 'Flask evidence has immediate 410 for /recovery/payment',
        ];
    }

    public function reconciliationReport(array $payload): array
    {
        $monthCycle = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($monthCycle)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle is required'];
        }

        $run = DB::selectOne(
            "SELECT id, run_status FROM util_billing_run
             WHERE month_cycle=? AND run_status IN ('LOCKED','APPROVED')
             ORDER BY CASE run_status WHEN 'LOCKED' THEN 1 WHEN 'APPROVED' THEN 2 ELSE 3 END, id DESC
             LIMIT 1",
            [$monthCycle]
        );

        if (!$run) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'No APPROVED/LOCKED billing run found'];
        }

        $billedTotalRow = DB::selectOne('SELECT ROUND(COALESCE(SUM(amount),0),2) AS billed_total FROM util_billing_line WHERE billing_run_id=?', [$run->id]);
        $billedTotal = (float)($billedTotalRow->billed_total ?? 0);

        $billedByUtility = DB::select(
            'SELECT utility_type, ROUND(COALESCE(SUM(amount),0),2) AS billed_amount
             FROM util_billing_line WHERE billing_run_id=? GROUP BY utility_type ORDER BY utility_type',
            [$run->id]
        );

        $billedByEmployee = DB::select(
            'SELECT employee_id, ROUND(COALESCE(SUM(amount),0),2) AS billed_amount
             FROM util_billing_line WHERE billing_run_id=? GROUP BY employee_id ORDER BY employee_id',
            [$run->id]
        );

        $recoveredTotal = 0.0;
        $recoveredByEmployee = [];
        try {
            $tot = DB::selectOne('SELECT ROUND(COALESCE(SUM(amount_paid),0),2) AS recovered_total FROM util_recovery_payment WHERE month_cycle=?', [$monthCycle]);
            $recoveredTotal = (float)($tot->recovered_total ?? 0);
            $empRows = DB::select('SELECT employee_id, ROUND(COALESCE(SUM(amount_paid),0),2) AS recovered_amount FROM util_recovery_payment WHERE month_cycle=? GROUP BY employee_id', [$monthCycle]);
            foreach ($empRows as $r) {
                $recoveredByEmployee[(string)$r->employee_id] = (float)($r->recovered_amount ?? 0);
            }
        } catch (\Throwable) {
            $recoveredTotal = 0.0;
            $recoveredByEmployee = [];
        }

        $byUtility = [];
        foreach ($billedByUtility as $b) {
            $billed = (float)($b->billed_amount ?? 0);
            $byUtility[] = [
                'utility_type' => (string)$b->utility_type,
                'billed_amount' => round($billed, 2),
                'recovered_amount' => 0.0,
                'outstanding_amount' => round($billed, 2),
            ];
        }

        $byEmployee = [];
        foreach ($billedByEmployee as $b) {
            $eid = (string)$b->employee_id;
            $billed = (float)($b->billed_amount ?? 0);
            $rec = (float)($recoveredByEmployee[$eid] ?? 0.0);
            $byEmployee[] = [
                'employee_id' => $eid,
                'billed_amount' => round($billed, 2),
                'recovered_amount' => round($rec, 2),
                'outstanding_amount' => round($billed - $rec, 2),
            ];
        }

        return [
            'status' => 'ok',
            'month_cycle' => $monthCycle,
            'billing_run_id' => (int)$run->id,
            'summary' => [
                'billed_total' => round($billedTotal, 2),
                'recovered_total' => round($recoveredTotal, 2),
                'outstanding_total' => round($billedTotal - $recoveredTotal, 2),
                'recovery_ratio' => $billedTotal == 0.0 ? 0 : round(($recoveredTotal / $billedTotal) * 100, 2),
            ],
            'by_utility' => $byUtility,
            'by_employee' => $byEmployee,
            'notes' => [
                'Recovery totals are sourced from util_recovery_payment.amount_paid.',
            ],
        ];
    }

    private function reportingRunId(string $monthCycle): ?int
    {
        $run = DB::selectOne(
            "SELECT id, run_status FROM util_billing_run
             WHERE month_cycle=? AND run_status IN ('LOCKED','APPROVED')
             ORDER BY CASE run_status WHEN 'LOCKED' THEN 1 WHEN 'APPROVED' THEN 2 ELSE 3 END, id DESC
             LIMIT 1",
            [$monthCycle]
        );
        return $run ? (int)$run->id : null;
    }

    public function monthlySummary(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];
        $runId = $this->reportingRunId($m);
        if (!$runId) return ['_http'=>404,'status'=>'error','month_cycle'=>$m,'rows'=>[],'error'=>'No APPROVED/LOCKED billing run found'];

        $rows = DB::select(
            'SELECT utility_type, ROUND(SUM(amount),2) as total_amount, ROUND(SUM(qty),4) as total_qty
             FROM util_billing_line WHERE billing_run_id=? GROUP BY utility_type ORDER BY utility_type',
            [$runId]
        );

        $grandTotal = 0.0;
        foreach ($rows as $r) {
            $grandTotal += (float)($r->total_amount ?? 0);
        }

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'billing_run_id' => $runId,
            'rows' => $rows,
            'summary' => [
                'utility_count' => count($rows),
                'grand_total_amount' => round($grandTotal, 2),
            ],
        ];
    }

    public function recoveryReport(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $runId = $this->reportingRunId($m);
        if (!$runId) return ['_http'=>404,'status'=>'error','month_cycle'=>$m,'rows'=>[],'error'=>'No APPROVED/LOCKED billing run found'];

        $rows = DB::select(
            'SELECT employee_id,
                    ROUND(COALESCE(SUM(amount),0),2) as billed_amount,
                    0.00 as recovered_amount,
                    ROUND(COALESCE(SUM(amount),0),2) as outstanding_amount
             FROM util_billing_line WHERE billing_run_id=? GROUP BY employee_id ORDER BY employee_id',
            [$runId]
        );

        $billedTotal = 0.0;
        foreach ($rows as $r) {
            $billedTotal += (float)($r->billed_amount ?? 0);
        }

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'billing_run_id' => $runId,
            'rows' => $rows,
            'summary' => [
                'billed_total' => round($billedTotal, 2),
                'recovered_total' => 0.0,
                'outstanding_total' => round($billedTotal, 2),
            ],
        ];
    }

    public function employeeBillSummary(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $runId = $this->reportingRunId($m);
        if (!$runId) return ['_http'=>404,'status'=>'error','error'=>'No APPROVED/LOCKED billing run found'];

        $rows = DB::select(
            'WITH bl AS (
                SELECT employee_id,
                       ROUND(COALESCE(SUM(CASE WHEN utility_type=\'ELEC\' THEN amount ELSE 0 END),0),2) AS electric_bill,
                       ROUND(COALESCE(SUM(CASE WHEN utility_type IN (\'WATER\',\'WATER_GENERAL\',\'WATER_DRINKING\') THEN amount ELSE 0 END),0),2) AS water_bill,
                       ROUND(COALESCE(SUM(CASE WHEN utility_type=\'SCHOOL_VAN\' THEN amount ELSE 0 END),0),2) AS school_van_bill,
                       ROUND(COALESCE(SUM(amount),0),2) AS total_bill
                FROM util_billing_line
                WHERE billing_run_id=?
                GROUP BY employee_id
            )
            SELECT bl.employee_id,
                   COALESCE(e.name, \'\') AS employee_name,
                   COALESCE(e.department, \'\') AS department,
                   CASE WHEN fd.company_id IS NULL THEN 0 ELSE 1 END AS has_family,
                   COALESCE(bl.electric_bill,0) AS electric_bill,
                   COALESCE(bl.water_bill,0) AS water_bill,
                   COALESCE(bl.school_van_bill,0) AS school_van_bill,
                   COALESCE(bl.total_bill,0) AS total_bill
            FROM bl
            LEFT JOIN employees_master e ON e.company_id = bl.employee_id
            LEFT JOIN (SELECT DISTINCT company_id FROM family_details WHERE month_cycle=?) fd ON fd.company_id = bl.employee_id
            ORDER BY bl.employee_id',
            [$runId, $m]
        );

        $total = 0.0;
        foreach ($rows as $row) {
            $total += (float)($row->total_bill ?? 0);
        }

        return [
            'status'=>'ok',
            'month_cycle'=>$m,
            'billing_run_id'=>$runId,
            'rows'=>$rows,
            'summary' => [
                'employee_count' => count($rows),
                'total_bill_amount' => round($total, 2),
            ],
        ];
    }

    public function vanReport(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $rows = DB::select(
            'SELECT employee_id, child_name, school_name, class_level, amount
             FROM util_school_van_monthly_charge WHERE month_cycle=? ORDER BY employee_id, child_name',
            [$m]
        );

        return ['month_cycle'=>$m,'rows'=>$rows];
    }

    public function elecSummary(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];
        $unitId = trim((string)($payload['unit_id'] ?? ''));

        $unitSql = 'SELECT month_cycle, unit_id, category, usage_units, rooms_count,
                           unit_free_units, net_units, elec_rate, unit_amount, total_attendance
                    FROM util_elec_unit_monthly_result WHERE month_cycle=?';
        $params = [$m];
        if ($unitId !== '') { $unitSql .= ' AND unit_id=?'; $params[] = $unitId; }
        $unitSql .= ' ORDER BY unit_id';

        $shareSql = 'SELECT s.month_cycle, s.unit_id, s.employee_id, e.name AS employee_name,
                            s.attendance, s.share_units, s.share_amount, s.allocation_method,
                            COALESCE(s.explain_usage_share_units,0) AS explain_usage_share_units,
                            COALESCE(s.explain_free_share_units,0) AS explain_free_share_units,
                            COALESCE(s.explain_billable_units,0) AS explain_billable_units
                     FROM util_elec_employee_share_monthly s
                     LEFT JOIN employees_master e ON e.company_id=s.employee_id
                     WHERE s.month_cycle=?';
        $shareParams = [$m];
        if ($unitId !== '') { $shareSql .= ' AND s.unit_id=?'; $shareParams[] = $unitId; }
        $shareSql .= ' ORDER BY s.unit_id, s.employee_id';

        $unitRows = DB::select($unitSql, $params);
        $shareRows = DB::select($shareSql, $shareParams);

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'unit_rows' => $unitRows,
            'share_rows' => $shareRows,
            'summary' => [
                'unit_count' => count($unitRows),
                'share_count' => count($shareRows),
            ],
        ];
    }

    public function elecCompute(array $payload): array
    {
        $result = $this->elecSummary($payload);
        if (($result['_http'] ?? 200) !== 200) {
            return $result;
        }

        return [
            'status' => 'ok',
            'month_cycle' => (string)$result['month_cycle'],
            'unit_rows' => $result['unit_rows'],
            'share_rows' => $result['share_rows'],
            'parity_note' => 'Parity endpoint active: /billing/elec/compute mapped to draft electric compute dataset.',
        ];
    }

    public function waterCompute(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $runId = $this->reportingRunId($m);
        if (!$runId) return ['_http'=>404,'status'=>'error','error'=>'No APPROVED/LOCKED billing run found'];

        $rows = DB::select(
            'SELECT employee_id, ROUND(SUM(amount),2) AS water_amount
             FROM util_billing_line
             WHERE billing_run_id=? AND utility_type IN (\'WATER\',\'WATER_GENERAL\',\'WATER_DRINKING\')
             GROUP BY employee_id
             ORDER BY employee_id',
            [$runId]
        );

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'billing_run_id' => $runId,
            'rows' => $rows,
        ];
    }

    private const WATER_ZONE_CODES = ['FAMILY_METER', 'BACHELOR_METER', 'ADMIN_METER', 'TANKER_ZONE'];
    private const WATER_COMMON_USE_REASON_CODES = [
        'Plants Irrigation', 'Garden Irrigation', 'Parks Irrigation', 'Cleaning/Washing',
        'Overflow', 'Line Damage/Leakage', 'Fire Drill / Emergency', 'Other',
    ];

    private function waterZoneForUnit(string $unitId, string $colonyType): string
    {
        $u = strtoupper(trim($unitId));
        $ct = strtolower(trim($colonyType));

        if (str_starts_with($u, 'CS-') || str_starts_with($u, 'PCER-') || str_starts_with($u, 'PSSR-') || str_contains($ct, 'admin block')) {
            return 'ADMIN_METER';
        }
        if (str_contains($ct, 'bachelor colony') || str_starts_with($u, 'WBC-') || str_starts_with($u, 'SBC-') || str_starts_with($u, 'NBC-')) {
            return 'BACHELOR_METER';
        }
        if (
            str_contains($ct, 'family colony') || str_contains($ct, 'weaving hostel/guest house') || str_contains($ct, 'spinning hostel/guest house')
            || str_starts_with($u, 'WH-') || str_starts_with($u, 'WHK-') || str_starts_with($u, 'SH-1-')
            || str_starts_with($u, 'WA-') || str_starts_with($u, 'WB-') || str_starts_with($u, 'WC-') || str_starts_with($u, 'WE-')
        ) {
            return 'FAMILY_METER';
        }

        return 'TANKER_ZONE';
    }

    private function dependentCountByUnit(string $monthCycle): array
    {
        $fdRows = DB::select('SELECT company_id, unit_id, COALESCE(spouse_count,0) AS spouse_count, COALESCE(children_count,0) AS children_count FROM family_details WHERE month_cycle=?', [$monthCycle]);
        $childRows = DB::select('SELECT company_id, COUNT(1) AS child_row_count FROM family_child_details WHERE month_cycle=? GROUP BY company_id', [$monthCycle]);

        $childMap = [];
        foreach ($childRows as $r) {
            $childMap[trim((string)($r->company_id ?? ''))] = (int)($r->child_row_count ?? 0);
        }

        $depByUnit = [];
        foreach ($fdRows as $r) {
            $unitId = trim((string)($r->unit_id ?? ''));
            if ($unitId === '') {
                continue;
            }
            $companyId = trim((string)($r->company_id ?? ''));
            $spouse = (int)($r->spouse_count ?? 0);
            $childrenFd = (int)($r->children_count ?? 0);
            $childrenChildRows = (int)($childMap[$companyId] ?? 0);
            $children = max($childrenFd, $childrenChildRows);
            $depByUnit[$unitId] = (int)($depByUnit[$unitId] ?? 0) + max($spouse + $children, 0);
        }

        return $depByUnit;
    }

    private function waterOccupancyRows(string $monthCycle): array
    {
        $empRows = DB::select('SELECT "Unit_ID" AS unit_id, COUNT(DISTINCT "CompanyID") AS emp_count FROM "Employees_Master" WHERE UPPER(COALESCE(NULLIF(TRIM("Active"),\'\'),\'YES\'))=\'YES\' AND COALESCE("Unit_ID",\'\')<>\'\' GROUP BY "Unit_ID"');
        $depMap = $this->dependentCountByUnit($monthCycle);

        $metaRows = [];
        foreach (['util_unit', 'util_units_reference'] as $tbl) {
            try {
                $metaRows = array_merge($metaRows, DB::select('SELECT unit_id, colony_type, MAX(COALESCE(free_water,0)) AS free_water_liters FROM '.$tbl.' GROUP BY unit_id, colony_type'));
            } catch (\Throwable $e) {
            }
        }

        $metaMap = [];
        foreach ($metaRows as $r) {
            $unitId = trim((string)($r->unit_id ?? ''));
            if ($unitId === '') continue;
            $existing = $metaMap[$unitId] ?? ['free_water_allowance_liters' => 0.0, 'colony_type' => ''];
            $existing['free_water_allowance_liters'] = max((float)$existing['free_water_allowance_liters'], (float)($r->free_water_liters ?? 0));
            $ct = trim((string)($r->colony_type ?? ''));
            if ($ct !== '' && ($existing['colony_type'] ?? '') === '') {
                $existing['colony_type'] = $ct;
            }
            $metaMap[$unitId] = $existing;
        }

        $rows = [];
        foreach ($empRows as $r) {
            $unitId = trim((string)($r->unit_id ?? ''));
            $emp = (int)($r->emp_count ?? 0);
            $dep = (int)($depMap[$unitId] ?? 0);
            $total = $emp + $dep;
            $ct = (string)(($metaMap[$unitId]['colony_type'] ?? '') ?: '');
            $zone = $this->waterZoneForUnit($unitId, $ct);
            $basePersons = $zone === 'FAMILY_METER' ? $total : $emp;

            $rows[] = [
                'unit_id' => $unitId,
                'colony_type' => $ct,
                'water_zone' => $zone,
                'emp_count' => $emp,
                'dependent_count' => $dep,
                'total_persons' => $total,
                'free_water_allowance_liters' => round(max($basePersons, 0) * 3000.0, 2),
            ];
        }

        usort($rows, fn ($a, $b) => strcmp((string)$a['unit_id'], (string)$b['unit_id']));
        return $rows;
    }

    private function waterZoneInputsForMonth(string $monthCycle): array
    {
        $rows = DB::select('SELECT month_cycle, water_zone, raw_liters, common_use_liters, reason_code, notes, source_ref FROM util_water_zone_monthly_input WHERE month_cycle=?', [$monthCycle]);

        $out = [];
        foreach (self::WATER_ZONE_CODES as $z) {
            $out[$z] = [
                'month_cycle' => $monthCycle,
                'water_zone' => $z,
                'raw_liters' => 0.0,
                'common_use_liters' => 0.0,
                'reason_code' => null,
                'notes' => '',
                'billable_residential_liters' => 0.0,
                'source_ref' => null,
            ];
        }

        foreach ($rows as $r) {
            $z = strtoupper(trim((string)($r->water_zone ?? '')));
            if (!isset($out[$z])) continue;
            $raw = (float)($r->raw_liters ?? 0);
            $common = (float)($r->common_use_liters ?? 0);
            $out[$z] = [
                'month_cycle' => $monthCycle,
                'water_zone' => $z,
                'raw_liters' => round($raw, 2),
                'common_use_liters' => round($common, 2),
                'reason_code' => $r->reason_code ?? null,
                'notes' => (string)($r->notes ?? ''),
                'billable_residential_liters' => round(max($raw - $common, 0.0), 2),
                'source_ref' => $r->source_ref ?? null,
            ];
        }

        return $out;
    }

    public function waterOccupancySnapshot(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $rows = $this->waterOccupancyRows($m);
        $zoneSummary = [];
        foreach ($rows as $x) {
            $z = (string)$x['water_zone'];
            $zoneSummary[$z] ??= ['units' => 0, 'total_persons' => 0];
            $zoneSummary[$z]['units']++;
            $zoneSummary[$z]['total_persons'] += (int)$x['total_persons'];
        }

        return ['status' => 'ok', 'month_cycle' => $m, 'zone_summary' => $zoneSummary, 'rows' => $rows];
    }

    public function waterZoneAdjustmentsGet(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $byZone = $this->waterZoneInputsForMonth($m);
        $rows = [];
        foreach (self::WATER_ZONE_CODES as $z) { $rows[] = $byZone[$z]; }

        return ['status' => 'ok', 'month_cycle' => $m, 'allowed_reason_codes' => self::WATER_COMMON_USE_REASON_CODES, 'rows' => $rows];
    }

    public function waterZoneAdjustmentsUpsert(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];
        if (count($rows) === 0) return ['_http'=>400,'status'=>'error','error'=>'rows[] is required'];

        foreach (array_values($rows) as $i => $r) {
            $z = strtoupper(trim((string)($r['water_zone'] ?? '')));
            if (!in_array($z, self::WATER_ZONE_CODES, true)) return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}].water_zone invalid"];

            if (!is_numeric((string)($r['raw_liters'] ?? 0)) || !is_numeric((string)($r['common_use_liters'] ?? 0))) {
                return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}] raw_liters/common_use_liters must be numeric"];
            }

            $raw = (float)($r['raw_liters'] ?? 0);
            $common = (float)($r['common_use_liters'] ?? 0);
            if ($raw < 0) return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}].raw_liters must be >= 0"];
            if ($common < 0) return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}].common_use_liters must be >= 0"];
            if ($common > $raw) return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}].common_use_liters cannot exceed raw_liters"];

            $reason = trim((string)($r['reason_code'] ?? ''));
            $reason = $reason === '' ? null : $reason;
            $notes = trim((string)($r['notes'] ?? ''));
            if ($reason !== null && !in_array($reason, self::WATER_COMMON_USE_REASON_CODES, true)) {
                return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}].reason_code invalid"];
            }
            if ($reason === 'Other' && $notes === '') {
                return ['_http'=>400,'status'=>'error','error'=>"rows[{$i}].notes required when reason_code=Other"];
            }

            DB::statement(
                'INSERT INTO util_water_zone_monthly_input(month_cycle, water_zone, raw_liters, common_use_liters, reason_code, notes, source_ref)
                 VALUES(?,?,?,?,?,?,?)
                 ON CONFLICT(month_cycle, water_zone) DO UPDATE SET
                   raw_liters=excluded.raw_liters,
                   common_use_liters=excluded.common_use_liters,
                   reason_code=excluded.reason_code,
                   notes=excluded.notes,
                   source_ref=excluded.source_ref,
                   updated_at=CURRENT_TIMESTAMP',
                [$m, $z, round($raw, 2), round($common, 2), $reason, $notes, trim((string)($r['source_ref'] ?? '')) ?: null]
            );
        }

        $byZone = $this->waterZoneInputsForMonth($m);
        $outRows = [];
        foreach (self::WATER_ZONE_CODES as $z) { $outRows[] = $byZone[$z]; }

        return ['status' => 'ok', 'month_cycle' => $m, 'rows' => $outRows];
    }

    public function waterAllocationPreview(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $zoneInputs = $this->waterZoneInputsForMonth($m);
        $billableZoneLiters = [];
        foreach (self::WATER_ZONE_CODES as $z) {
            $billableZoneLiters[$z] = (float)($zoneInputs[$z]['billable_residential_liters'] ?? 0);
        }

        $rows = $this->waterOccupancyRows($m);
        $zoneBasis = ['FAMILY_METER'=>0.0,'BACHELOR_METER'=>0.0,'ADMIN_METER'=>0.0,'TANKER_ZONE'=>0.0];
        foreach ($rows as $x) {
            $z = (string)$x['water_zone'];
            $basis = (float)($z === 'FAMILY_METER' ? ($x['total_persons'] ?? 0) : ($x['emp_count'] ?? 0));
            $zoneBasis[$z] += max($basis, 0.0);
        }

        $out = [];
        $netAlloc = 0.0;
        foreach ($rows as $x) {
            $z = (string)$x['water_zone'];
            $basisPersons = (float)($z === 'FAMILY_METER' ? ($x['total_persons'] ?? 0) : ($x['emp_count'] ?? 0));
            $denom = (float)($zoneBasis[$z] ?? 0);
            $gross = $denom == 0.0 ? 0.0 : ((float)$billableZoneLiters[$z] * $basisPersons / $denom);
            $freeAllow = (float)($x['free_water_allowance_liters'] ?? 0);
            $unitNet = max($gross - $freeAllow, 0.0);
            $netAlloc += $unitNet;
            $empCount = (int)($x['emp_count'] ?? 0);
            $perEmp = $empCount <= 0 ? 0.0 : ($unitNet / $empCount);

            $out[] = [
                'unit_id' => $x['unit_id'],
                'colony_type' => $x['colony_type'],
                'water_zone' => $z,
                'emp_count' => $empCount,
                'dependent_count' => (int)$x['dependent_count'],
                'total_persons' => (int)$x['total_persons'],
                'basis_persons' => round($basisPersons, 4),
                'unit_gross_water_liters' => round($gross, 2),
                'unit_free_water_allowance_liters' => round($freeAllow, 2),
                'unit_net_water_liters' => round($unitNet, 2),
                'per_employee_water_share_liters' => round($perEmp, 2),
            ];
        }

        $zoneInputRows = [];
        foreach (self::WATER_ZONE_CODES as $z) { $zoneInputRows[] = $zoneInputs[$z]; }

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'inputs' => [
                'zone_inputs' => $zoneInputRows,
                'allocation_basis' => 'zone-wise basis_persons (family=employees+dependents, non-family=employees)',
                'measurement_unit' => 'liters',
                'meter_reading_unit' => 'cubic_meter',
                'meter_to_liters_factor' => 1000,
                'billable_residential_formula' => 'max(raw_liters - common_use_liters, 0)',
                'free_allowance_rule' => 'applied once per unit (liters locked)',
            ],
            'zone_basis_persons' => $zoneBasis,
            'summary' => ['units' => count($out), 'net_allocated' => round($netAlloc, 2)],
            'rows' => $out,
        ];
    }

    public function run(array $payload): array
    {
        $monthCycle = $this->normalizeMonthCycle((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($monthCycle)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle must be MM-YYYY'];
        }

        $monthState = $this->monthState($monthCycle);
        if ($monthState === null) {
            return ['_http' => 409, 'status' => 'error', 'error' => 'month_cycle not found; initialize/open month before billing run'];
        }
        if ($monthState === 'LOCKED') {
            return ['_http' => 409, 'status' => 'error', 'error' => "month_cycle {$monthCycle} is LOCKED; billing run is blocked"];
        }

        $runKey = trim((string)($payload['run_key'] ?? ''));
        if ($runKey === '') {
            $runKey = $monthCycle.':'.substr(bin2hex(random_bytes(8)), 0, 8);
        }
        $actor = (int)($payload['actor_user_id'] ?? session('actor_user_id') ?? session('user_id') ?? 1);

        DB::statement("INSERT OR IGNORE INTO util_billing_run(month_cycle,run_key,run_status,started_by_user_id) VALUES(?,?,'DRAFT',?)", [$monthCycle, $runKey, $actor]);
        $run = DB::selectOne('SELECT id FROM util_billing_run WHERE month_cycle=? AND run_key=?', [$monthCycle, $runKey]);
        $runId = (int)($run->id ?? 0);
        if ($runId <= 0) {
            return ['_http' => 500, 'status' => 'error', 'error' => 'billing run id resolution failed'];
        }

        DB::statement("INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
             SELECT ?, month_cycle, employee_id, 'ELEC', elec_units,
                    CASE WHEN elec_units=0 THEN 0 ELSE ROUND(elec_amount/elec_units,4) END,
                    elec_amount, 'util_formula_result'
             FROM util_formula_result WHERE month_cycle=?
             ON CONFLICT(billing_run_id,employee_id,utility_type) DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount", [$runId, $monthCycle]);

        // Flask-authoritative sequence: ELEC insert occurs, then run lines are cleared before rebuilding persisted summary set.
        DB::statement('DELETE FROM util_billing_line WHERE billing_run_id=?', [$runId]);

        DB::statement("INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
             SELECT ?, month_cycle, employee_id, 'WATER_GENERAL', chargeable_general_water_liters,
                    CASE WHEN chargeable_general_water_liters=0 THEN 0 ELSE ROUND(water_general_amount/chargeable_general_water_liters,4) END,
                    water_general_amount, 'util_formula_result'
             FROM util_formula_result WHERE month_cycle=?
             ON CONFLICT(billing_run_id,employee_id,utility_type) DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount", [$runId, $monthCycle]);

        DB::statement("INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
             SELECT ?, month_cycle, employee_id, 'WATER_DRINKING', billed_liters, rate, amount, 'util_drinking_formula_result'
             FROM util_drinking_formula_result WHERE month_cycle=?
             ON CONFLICT(billing_run_id,employee_id,utility_type) DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount", [$runId, $monthCycle]);

        DB::statement("INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
             SELECT ?, month_cycle, employee_id, 'SCHOOL_VAN', COUNT(*),
                    CASE WHEN COUNT(*)=0 THEN 0 ELSE ROUND(SUM(amount)/COUNT(*),2) END,
                    SUM(amount), 'util_school_van_monthly_charge'
             FROM util_school_van_monthly_charge WHERE month_cycle=? GROUP BY month_cycle, employee_id
             ON CONFLICT(billing_run_id,employee_id,utility_type) DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount", [$runId, $monthCycle]);

        // Flask parity: generated run becomes APPROVED so reporting path can open without removed approve workflow.
        DB::update("UPDATE util_billing_run SET run_status='APPROVED' WHERE id=?", [$runId]);

        return ['status' => 'ok', 'run_id' => $runId, 'run_key' => $runKey, 'run_status' => 'APPROVED'];
    }

    public function electricV1Outputs(array $payload): array
    {
        $summary = $this->elecSummary($payload + ['unit_id' => (string)($payload['unit_id'] ?? '')]);
        if (($summary['_http'] ?? 200) !== 200) {
            return $summary;
        }

        return [
            'status' => 'ok',
            'month_cycle' => (string)$summary['month_cycle'],
            'unit_rows' => $summary['unit_rows'],
            'share_rows' => $summary['share_rows'],
            'summary' => $summary['summary'] ?? ['unit_count' => 0, 'share_count' => 0],
        ];
    }

    public function electricV1Run(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle is required'];
        }

        $result = $this->elecCompute(['month_cycle' => $m, 'unit_id' => (string)($payload['unit_id'] ?? '')]);
        if (($result['_http'] ?? 200) !== 200) {
            return $result;
        }

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'unit_rows' => $result['unit_rows'] ?? [],
            'share_rows' => $result['share_rows'] ?? [],
            'run_status' => 'computed',
        ];
    }

    public function fingerprint(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        $run = DB::selectOne(
            "SELECT id, run_key, run_status FROM util_billing_run
             WHERE month_cycle=? AND run_status IN ('LOCKED','APPROVED')
             ORDER BY CASE run_status WHEN 'LOCKED' THEN 1 WHEN 'APPROVED' THEN 2 ELSE 3 END, id DESC
             LIMIT 1",
            [$m]
        );

        if (!$run) {
            return ['_http'=>404,'status'=>'error','error'=>'No APPROVED/LOCKED billing run found'];
        }

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'billing_run_id' => (int)$run->id,
            'run_status' => (string)$run->run_status,
            'fingerprint' => (string)($run->run_key ?? ''),
        ];
    }

    public function adjustmentsList(array $payload): array
    {
        $m = trim((string)($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'rows' => [],
            'parity_note' => 'List endpoint active for parity; create/approve adjustment flows remain intentionally removed (410).',
        ];
    }

    public function printEmployee(string $monthCycle, string $employeeId): array
    {
        $m = trim($monthCycle);
        $e = trim($employeeId);

        if (!$this->monthValid($m)) return ['_http'=>400,'status'=>'error','error'=>'month_cycle is required'];
        if ($e === '') return ['_http'=>400,'status'=>'error','error'=>'employee_id is required'];

        $runId = $this->reportingRunId($m);
        if (!$runId) return ['_http'=>404,'status'=>'error','error'=>'No APPROVED/LOCKED billing run found'];

        $rows = DB::select(
            'SELECT utility_type, ROUND(COALESCE(qty,0),4) AS qty, ROUND(COALESCE(amount,0),2) AS amount, COALESCE(source_ref, \'\') AS source_ref
             FROM util_billing_line
             WHERE billing_run_id=? AND employee_id=?
             ORDER BY utility_type',
            [$runId, $e]
        );

        $totalRow = DB::selectOne(
            'SELECT ROUND(COALESCE(SUM(amount),0),2) AS total_amount
             FROM util_billing_line
             WHERE billing_run_id=? AND employee_id=?',
            [$runId, $e]
        );

        return [
            'status' => 'ok',
            'month_cycle' => $m,
            'billing_run_id' => $runId,
            'employee_id' => $e,
            'rows' => $rows,
            'total_amount' => round((float)($totalRow->total_amount ?? 0), 2),
        ];
    }

    public function exportExcelReconciliation(array $payload): array
    {
        $rep = $this->reconciliationReport($payload);
        if (($rep['_http'] ?? 200) !== 200) {
            return $rep;
        }

        if (!class_exists('\XLSXWriter')) {
            return ['_http' => 500, 'status' => 'error', 'error' => 'XLSX writer package not available'];
        }

        $writer = new \XLSXWriter();
        $writer->setAuthor('mbs_project');
        $writer->writeSheetRow('reconciliation', ['employee_id', 'billed_amount', 'recovered_amount', 'outstanding_amount']);
        foreach ($rep['by_employee'] as $r) {
            $writer->writeSheetRow('reconciliation', [
                (string)$r['employee_id'],
                (float)$r['billed_amount'],
                (float)$r['recovered_amount'],
                (float)$r['outstanding_amount'],
            ]);
        }

        return [
            'status' => 'ok',
            'month_cycle' => $rep['month_cycle'],
            'filename' => 'reconciliation_'.$rep['month_cycle'].'.xlsx',
            'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'content' => $this->xlsxBytes($writer, 'mbs_recon_'),
            'parity_note' => 'XLSX export enabled for active reconciliation export surface.',
        ];
    }

    public function exportExcelMonthlySummary(array $payload): array
    {
        $rep = $this->monthlySummary($payload);
        if (($rep['_http'] ?? 200) !== 200) {
            return $rep;
        }

        if (!class_exists('\XLSXWriter')) {
            return ['_http' => 500, 'status' => 'error', 'error' => 'XLSX writer package not available'];
        }

        $writer = new \XLSXWriter();
        $writer->setAuthor('mbs_project');
        $writer->writeSheetRow('monthly_summary', ['utility_type', 'total_amount', 'total_qty']);
        foreach ($rep['rows'] as $row) {
            $writer->writeSheetRow('monthly_summary', [
                (string)($row->utility_type ?? ''),
                (float)($row->total_amount ?? 0),
                (float)($row->total_qty ?? 0),
            ]);
        }

        return [
            'status' => 'ok',
            'month_cycle' => $rep['month_cycle'],
            'filename' => 'monthly_summary_'.$rep['month_cycle'].'.xlsx',
            'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'content' => $this->xlsxBytes($writer, 'mbs_mon_'),
        ];
    }

    public function exportPdfMonthlySummary(array $payload): array
    {
        $rep = $this->monthlySummary($payload);
        if (($rep['_http'] ?? 200) !== 200) {
            return $rep;
        }

        $lines = [
            'Monthly Summary - '.$rep['month_cycle'],
            'Billing Run ID: '.(string)$rep['billing_run_id'],
            '',
            'Utility | Amount | Qty',
        ];
        foreach ($rep['rows'] as $row) {
            $lines[] = sprintf(
                '%s | %.2f | %.4f',
                (string)($row->utility_type ?? ''),
                (float)($row->total_amount ?? 0),
                (float)($row->total_qty ?? 0)
            );
        }

        $pdfBody = implode("\n", $lines);
        $content = "%PDF-1.4\n".
            "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n".
            "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n".
            "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources <<>> >>endobj\n".
            "4 0 obj<< /Length ".strlen($pdfBody)." >>stream\n".$pdfBody."\nendstream endobj\n".
            "xref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \n0000000224 00000 n \n".
            "trailer<< /Root 1 0 R /Size 5 >>\nstartxref\n".(224 + strlen($pdfBody))."\n%%EOF";

        return [
            'status' => 'ok',
            'month_cycle' => $rep['month_cycle'],
            'filename' => 'monthly_summary_'.$rep['month_cycle'].'.pdf',
            'content_type' => 'application/pdf',
            'content' => $content,
        ];
    }

    private function xlsxBytes(\XLSXWriter $writer, string $prefix): string
    {
        $tmp = tempnam(sys_get_temp_dir(), $prefix);
        $xlsx = $tmp.'.xlsx';
        @unlink($tmp);
        $writer->writeToFile($xlsx);
        $bytes = file_get_contents($xlsx) ?: '';
        @unlink($xlsx);

        return $bytes;
    }
}

