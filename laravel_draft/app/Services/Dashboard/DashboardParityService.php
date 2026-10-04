<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class DashboardParityService
{
    private function qOne(string $sql, array $params = []): ?object
    {
        try {
            return DB::selectOne($sql, $params);
        } catch (\Throwable) {
            return null;
        }
    }

    private function qAll(string $sql, array $params = []): array
    {
        try {
            return DB::select($sql, $params);
        } catch (\Throwable) {
            return [];
        }
    }

    public function resolveMonthCycle(?string $monthCycle = null): ?string
    {
        $monthCycle = trim((string) ($monthCycle ?? ''));

        // Explicitly selected month always wins.
        if ($monthCycle !== '') {
            return $monthCycle;
        }

        /*
         * Default dashboard month:
         * latest billing cycle whose cycle end date has already passed/reached.
         *
         * Example on 22-Sep-2026:
         * 09-2026 ends 15-Sep-2026 -> selected
         * 10-2026 ends 15-Oct-2026 -> not selected yet
         */
        $row = $this->qOne(
            "SELECT month_cycle
             FROM util_month_cycle
             WHERE cycle_end_date <= CURDATE()
             ORDER BY cycle_end_date DESC
             LIMIT 1"
        );

        if ($row && !empty($row->month_cycle)) {
            return (string) $row->month_cycle;
        }

        // Safety fallback if no completed cycle exists.
        $row = $this->qOne(
            "SELECT month_cycle
             FROM util_month_cycle
             ORDER BY cycle_end_date DESC
             LIMIT 1"
        );

        return ($row && !empty($row->month_cycle))
            ? (string) $row->month_cycle
            : null;
    }

    private function missingBillMonths(?string $resolvedMonth): array
    {
        // current calendar month as MM-YYYY
        $current = date('m-Y');
        // collect months that DO have a billing run
        $runRows = $this->qAll("SELECT DISTINCT month_cycle FROM bill_runs WHERE month_cycle IS NOT NULL AND month_cycle <> '' AND status <> 'VOIDED'");
        $haveRun = [];
        foreach ($runRows as $r) { $haveRun[(string) $r->month_cycle] = true; }
        // start from resolved month (or current if none), walk forward to current
        $start = $resolvedMonth ?: $current;
        $missing = [];
        // parse start
        [$sm, $sy] = array_map('intval', explode('-', $start));
        [$cm, $cy] = array_map('intval', explode('-', $current));
        $m = $sm; $y = $sy;
        $guard = 0;
        while (($y < $cy || ($y === $cy && $m <= $cm)) && $guard < 120) {
            $key = sprintf('%02d-%04d', $m, $y);
            if (empty($haveRun[$key]) && $key !== $resolvedMonth) {
                $missing[] = $key;
            }
            $m++; if ($m > 12) { $m = 1; $y++; }
            $guard++;
        }
        // also include current month if it has no run and not resolved
        if (empty($haveRun[$current]) && $current !== $resolvedMonth && !in_array($current, $missing, true)) {
            $missing[] = $current;
        }
        return array_values(array_unique($missing));
    }

    public function colonyKpis(?string $monthCycle = null): array
    {
        $month = $this->resolveMonthCycle($monthCycle);

        $resident = $this->qOne(
            "SELECT
                COUNT(*) AS total_units,
                SUM(CASE WHEN residence_type LIKE 'House %' THEN 1 ELSE 0 END) AS house_units,
                SUM(CASE WHEN residence_type = 'Bachelor' THEN 1 ELSE 0 END) AS bachelor_units,
                SUM(CASE WHEN residence_type = 'Hostel' THEN 1 ELSE 0 END) AS hostel_units,
                SUM(CASE WHEN residence_type = 'Container' THEN 1 ELSE 0 END) AS container_units,
                SUM(CASE WHEN residence_type IS NULL OR residence_type = ''
                          OR NOT (residence_type LIKE 'House %'
                                  OR residence_type = 'Bachelor'
                                  OR residence_type = 'Hostel'
                                  OR residence_type = 'Container')
                     THEN 1 ELSE 0 END) AS uncategorized_units
             FROM util_unit_room_snapshot
             WHERE month_cycle = (SELECT MAX(month_cycle) FROM util_unit_room_snapshot)"
        );

        if (!$month) {
            return [
                'status' => 'ok',
                'month_cycle' => null,
                'kpis' => [
                    'employees_billed' => 0,
                    'total_billed' => 0.0,
                    'family_members' => 0,
                    'van_kids' => 0,
                    'resolved_month' => null,
                    'current_month' => date('m-Y'),
                    'missing_bill_months' => $this->missingBillMonths(null),
                    'total_units' => (int) ($resident->total_units ?? 0),
                    'house_units' => (int) ($resident->house_units ?? 0),
                    'bachelor_units' => (int) ($resident->bachelor_units ?? 0),
                    'hostel_units' => (int) ($resident->hostel_units ?? 0),
                    'container_units' => (int) ($resident->container_units ?? 0),
                    'uncategorized_units' => (int) ($resident->uncategorized_units ?? 0),
                ],
            ];
        }

        $billed = $this->qOne(
            'SELECT COUNT(DISTINCT employee_id) AS employees_billed, ROUND(COALESCE(SUM(amount),0),2) AS total_billed FROM util_billing_line WHERE month_cycle=?',
            [$month]
        );

        $families = $this->qOne('SELECT COUNT(*) AS family_members FROM family_details WHERE month_cycle=?', [$month]);
        $vanKids = $this->qOne('SELECT COUNT(*) AS van_kids FROM util_school_van_monthly_charge WHERE month_cycle=?', [$month]);
        // Workflow attention counts from ReadinessService
        $wf = ['must_fix'=>0,'please_review'=>0,'ready'=>0,'missing_readings'=>0,'missing_rooms'=>0,'rate_missing'=>0];
        try {
            $rdy = app(\App\Services\Billing\ControlRoom\ReadinessService::class)->summary($month, false);
            $blk = $rdy['blockers'] ?? [];
            $codes = array_map(fn($b) => is_array($b) ? ($b['code'] ?? '') : ($b->code ?? ''), $blk);
            $wf['must_fix'] = count($blk);
            $wf['please_review'] = count($rdy['warnings'] ?? []);
            $wf['ready'] = !empty($rdy['isReady']) ? 1 : 0;
            $wf['missing_readings'] = count(array_intersect($codes, ['NO_CURRENT_READINGS','NO_PREVIOUS_READINGS','NO_ACTIVE_METERS']));
            $wf['missing_rooms'] = in_array('NO_ROOM_ALLOWANCE', $codes, true) ? 1 : 0;
            $wf['rate_missing'] = in_array('NO_ELECTRIC_RATE', $codes, true) ? 1 : 0;
        } catch (\Throwable $e) { /* keep zeros on any failure */ }
        // Recent activity timestamps (only from sources that have data)
        $act = [];
        $rd = $this->qOne("SELECT MAX(reading_date) AS ts FROM util_meter_readings");
        $ad = $this->qOne("SELECT MAX(created_at) AS ts FROM electric_active_days_monthly");
        $em = $this->qOne("SELECT MAX(updated_at) AS ts FROM employees_master");
        $fmt = function($ts){ return $ts ? date('M j, g:i A', strtotime($ts)) : null; };
        $act['last_reading'] = ($rd && $rd->ts) ? 'All Meters Updated' : null;
        $act['last_reading_time'] = $fmt($rd->ts ?? null);
        $act['last_upload'] = ($ad && $ad->ts) ? 'Active Days Uploaded' : null;
        $act['last_upload_time'] = $fmt($ad->ts ?? null);
        $act['last_emp_update'] = ($em && $em->ts) ? 'Employee Record Updated' : null;
        $act['last_emp_time'] = $fmt($em->ts ?? null);

        return [
            'status' => 'ok',
            'month_cycle' => $month,
            'kpis' => [
                'employees_billed' => (int) ($billed->employees_billed ?? 0),
                'total_billed' => (float) ($billed->total_billed ?? 0),
                'family_members' => (int) ($families->family_members ?? 0),
                'van_kids' => (int) ($vanKids->van_kids ?? 0),
                'resolved_month' => $month,
                'current_month' => date('m-Y'),
                'missing_bill_months' => $this->missingBillMonths($month),
                'must_fix' => $wf['must_fix'],
                'please_review' => $wf['please_review'],
                'ready' => $wf['ready'],
                'missing_readings' => $wf['missing_readings'],
                'missing_rooms' => $wf['missing_rooms'],
                'rate_missing' => $wf['rate_missing'],
                'last_reading' => $act['last_reading'] ?? null,
                'last_reading_time' => $act['last_reading_time'] ?? null,
                'last_upload' => $act['last_upload'] ?? null,
                'last_upload_time' => $act['last_upload_time'] ?? null,
                'last_emp_update' => $act['last_emp_update'] ?? null,
                'last_emp_time' => $act['last_emp_time'] ?? null,
                'total_units' => (int) ($resident->total_units ?? 0),
                'house_units' => (int) ($resident->house_units ?? 0),
                'bachelor_units' => (int) ($resident->bachelor_units ?? 0),
                'hostel_units' => (int) ($resident->hostel_units ?? 0),
                'container_units' => (int) ($resident->container_units ?? 0),
                'uncategorized_units' => (int) ($resident->uncategorized_units ?? 0),
            ],
        ];
    }

    public function familyMembers(?string $monthCycle = null): array
    {
        $month = $this->resolveMonthCycle($monthCycle);
        if (!$month) {
            return ['status' => 'ok', 'month_cycle' => null, 'rows' => []];
        }

        $rows = $this->qAll(
            'SELECT company_id AS employee_id, family_member_name, relation, age
             FROM family_details
             WHERE month_cycle=?
             ORDER BY company_id, family_member_name',
            [$month]
        );

        return ['status' => 'ok', 'month_cycle' => $month, 'rows' => $rows];
    }

    public function vanKids(?string $monthCycle = null): array
    {
        $month = $this->resolveMonthCycle($monthCycle);
        if (!$month) {
            return ['status' => 'ok', 'month_cycle' => null, 'rows' => []];
        }

        $rows = $this->qAll(
            'SELECT employee_id, child_name, school_name, class_level, amount
             FROM util_school_van_monthly_charge
             WHERE month_cycle=?
             ORDER BY employee_id, child_name',
            [$month]
        );

        return ['status' => 'ok', 'month_cycle' => $month, 'rows' => $rows];
    }

    public function reportsSummary(?string $monthCycle = null): array
    {
        $month = $this->resolveMonthCycle($monthCycle);
        if (!$month) {
            return ['month_cycle' => null, 'rows' => []];
        }

        $rows = $this->qAll(
            'SELECT utility_type, ROUND(COALESCE(SUM(amount),0),2) AS total_amount
             FROM util_billing_line
             WHERE month_cycle=?
             GROUP BY utility_type
             ORDER BY utility_type',
            [$month]
        );

        return ['month_cycle' => $month, 'rows' => $rows];
    }

    public function reconciliation(?string $monthCycle = null): array
    {
        $month = $this->resolveMonthCycle($monthCycle);
        if (!$month) {
            return ['month_cycle' => null, 'rows' => []];
        }

        $rows = $this->qAll(
            'SELECT b.employee_id,
                    ROUND(COALESCE(b.billed,0),2) AS billed,
                    ROUND(COALESCE(r.recovered,0),2) AS recovered,
                    ROUND(COALESCE(b.billed,0)-COALESCE(r.recovered,0),2) AS outstanding
             FROM (
                 SELECT employee_id, SUM(amount) AS billed
                 FROM util_billing_line
                 WHERE month_cycle=?
                 GROUP BY employee_id
             ) b
             LEFT JOIN (
                 SELECT employee_id, SUM(amount_paid) AS recovered
                 FROM util_recovery_payment
                 WHERE month_cycle=?
                 GROUP BY employee_id
             ) r ON r.employee_id = b.employee_id
             ORDER BY b.employee_id',
            [$month, $month]
        );

        return ['month_cycle' => $month, 'rows' => $rows];
    }

    public function monthControl(): array
    {
        $rows = $this->qAll(
            'SELECT month_cycle, state, locked_at, finalized_at
             FROM util_month_cycle
             ORDER BY substr(month_cycle, 4, 4) DESC, substr(month_cycle, 1, 2) DESC'
        );

        return ['rows' => $rows];
    }
}
