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
        if ($monthCycle !== '') {
            return $monthCycle;
        }

        $row = $this->qOne("SELECT month_cycle FROM util_billing_run ORDER BY id DESC LIMIT 1");

        return $row ? (string) $row->month_cycle : null;
    }

    public function colonyKpis(?string $monthCycle = null): array
    {
        $month = $this->resolveMonthCycle($monthCycle);

        if (!$month) {
            return [
                'status' => 'ok',
                'month_cycle' => null,
                'kpis' => [
                    'employees_billed' => 0,
                    'total_billed' => 0.0,
                    'family_members' => 0,
                    'van_kids' => 0,
                ],
            ];
        }

        $billed = $this->qOne(
            'SELECT COUNT(DISTINCT employee_id) AS employees_billed, ROUND(COALESCE(SUM(amount),0),2) AS total_billed FROM util_billing_line WHERE month_cycle=?',
            [$month]
        );

        $families = $this->qOne('SELECT COUNT(*) AS family_members FROM family_details WHERE month_cycle=?', [$month]);
        $vanKids = $this->qOne('SELECT COUNT(*) AS van_kids FROM util_school_van_monthly_charge WHERE month_cycle=?', [$month]);

        return [
            'status' => 'ok',
            'month_cycle' => $month,
            'kpis' => [
                'employees_billed' => (int) ($billed->employees_billed ?? 0),
                'total_billed' => (float) ($billed->total_billed ?? 0),
                'family_members' => (int) ($families->family_members ?? 0),
                'van_kids' => (int) ($vanKids->van_kids ?? 0),
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
