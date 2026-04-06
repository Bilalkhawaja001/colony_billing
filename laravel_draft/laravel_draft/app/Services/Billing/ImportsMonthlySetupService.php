<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class ImportsMonthlySetupService
{
    public function ingestPreview(array $payload): array
    {
        return [
            'status' => 'ok',
            'month_cycle' => $payload['month_cycle'],
            'preview' => [
                'rows_received' => (int)($payload['rows_received'] ?? 0),
                'estimated_errors' => 0,
            ],
            'token' => 'imp_'.substr(bin2hex(random_bytes(8)), 0, 12),
        ];
    }

    public function markValidated(array $payload): array
    {
        return [
            'status' => 'ok',
            'token' => $payload['token'],
            'validated_by' => (int)(session('actor_user_id') ?? session('user_id') ?? 0),
        ];
    }

    public function unitIdAliases(): array
    {
        try {
            $rows = DB::select('SELECT source_unit_id, canonical_unit_id FROM unit_id_aliases ORDER BY source_unit_id');
        } catch (\Throwable) {
            $rows = [];
        }

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function errorReport(string $token): array
    {
        if (trim($token) === '') {
            return ['_http' => 404, 'status' => 'error', 'error' => 'report token not found'];
        }

        try {
            $rows = DB::select('SELECT row_no, error_code, error_message FROM import_error_report WHERE token=? ORDER BY row_no', [$token]);
        } catch (\Throwable) {
            $rows = [];
        }

        return ['status' => 'ok', 'token' => $token, 'rows' => $rows];
    }

    public function monthlyRatesInitialize(array $payload): array
    {
        $month = (string)($payload['month_cycle'] ?? '');
        $seedFromPrevious = (bool)($payload['seed_from_previous'] ?? true);

        $existing = DB::selectOne('SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate FROM util_monthly_rates_config WHERE month_cycle=? LIMIT 1', [$month]);
        if ($existing) {
            return [
                'status' => 'ok',
                'month_cycle' => $month,
                'seeded' => false,
                'already_exists' => true,
                'rates' => [
                    'elec_rate' => (float)($existing->elec_rate ?? 0),
                    'water_general_rate' => (float)($existing->water_general_rate ?? 0),
                    'water_drinking_rate' => (float)($existing->water_drinking_rate ?? 0),
                    'school_van_rate' => (float)($existing->school_van_rate ?? 0),
                ],
            ];
        }

        $seed = [
            'elec_rate' => 0.0,
            'water_general_rate' => 0.0,
            'water_drinking_rate' => 0.0,
            'school_van_rate' => 0.0,
        ];

        if ($seedFromPrevious) {
            $prev = DB::selectOne('SELECT elec_rate, water_general_rate, water_drinking_rate, school_van_rate FROM util_monthly_rates_config ORDER BY id DESC LIMIT 1');
            if ($prev) {
                $seed = [
                    'elec_rate' => (float)($prev->elec_rate ?? 0),
                    'water_general_rate' => (float)($prev->water_general_rate ?? 0),
                    'water_drinking_rate' => (float)($prev->water_drinking_rate ?? 0),
                    'school_van_rate' => (float)($prev->school_van_rate ?? 0),
                ];
            }
        }

        DB::statement(
            'INSERT INTO util_monthly_rates_config(month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate) VALUES(?, ?, ?, ?, ?)',
            [$month, $seed['elec_rate'], $seed['water_general_rate'], $seed['water_drinking_rate'], $seed['school_van_rate']]
        );

        return ['status' => 'ok', 'month_cycle' => $month, 'seeded' => true, 'already_exists' => false, 'rates' => $seed];
    }

    public function monthlyRatesConfig(array $payload): array
    {
        $row = DB::selectOne(
            'SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate, created_at, updated_at FROM util_monthly_rates_config WHERE month_cycle=? LIMIT 1',
            [$payload['month_cycle']]
        );

        if (!$row) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'config not found'];
        }

        return ['status' => 'ok', 'row' => $row];
    }

    public function monthlyRatesHistory(int $limit = 12): array
    {
        $limit = max(1, min($limit, 120));
        $rows = DB::select(
            'SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate, created_at, updated_at FROM util_monthly_rates_config ORDER BY month_cycle DESC LIMIT '.$limit
        );

        return ['status' => 'ok', 'rows' => $rows];
    }

    private function ratesTuple(array $payload): array
    {
        $tuple = [
            'elec_rate' => (float)($payload['elec_rate'] ?? 0),
            'water_general_rate' => (float)($payload['water_general_rate'] ?? 0),
            'water_drinking_rate' => (float)($payload['water_drinking_rate'] ?? 0),
            'school_van_rate' => (float)($payload['school_van_rate'] ?? 0),
        ];

        foreach ((array)($payload['rates'] ?? []) as $rateRow) {
            $utility = strtoupper(trim((string)($rateRow['utility_type'] ?? '')));
            $value = (float)($rateRow['rate'] ?? 0);
            if ($utility === 'ELEC') $tuple['elec_rate'] = $value;
            if (in_array($utility, ['WATER', 'WATER_GENERAL'], true)) $tuple['water_general_rate'] = $value;
            if ($utility === 'WATER_DRINKING') $tuple['water_drinking_rate'] = $value;
            if (in_array($utility, ['SCHOOL_VAN', 'VAN'], true)) $tuple['school_van_rate'] = $value;
        }

        return $tuple;
    }

    public function monthlyRatesConfigUpsert(array $payload): array
    {
        $month = (string)$payload['month_cycle'];
        $rates = $this->ratesTuple($payload);

        DB::statement(
            'INSERT INTO util_monthly_rates_config(month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate)
             VALUES(?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
             elec_rate=VALUES(elec_rate),
             water_general_rate=VALUES(water_general_rate),
             water_drinking_rate=VALUES(water_drinking_rate),
             school_van_rate=VALUES(school_van_rate),
             updated_at=CURRENT_TIMESTAMP',
            [$month, $rates['elec_rate'], $rates['water_general_rate'], $rates['water_drinking_rate'], $rates['school_van_rate']]
        );

        return [
            'status' => 'ok',
            'upserted' => true,
            'month_cycle' => $month,
            'actor_user_id' => (int)(session('actor_user_id') ?? session('user_id') ?? 0),
        ];
    }

    public function ratesApprove(array $payload): array
    {
        try {
            DB::statement('INSERT INTO util_month_cycle(month_cycle, state) VALUES(?, ?) ON CONFLICT(month_cycle) DO UPDATE SET state=excluded.state', [$payload['month_cycle'], 'APPROVAL']);
        } catch (\Throwable) {
            // non-fatal in draft schema
        }

        return ['status' => 'ok', 'month_cycle' => $payload['month_cycle'], 'approved' => true, 'state' => 'APPROVAL'];
    }

    public function monthlyVariableGet(array $payload): array
    {
        $monthCycle = trim((string)($payload['month_cycle'] ?? ''));
        $expenseType = strtoupper(trim((string)($payload['expense_type'] ?? '')));

        $sql = 'SELECT id, month_cycle, expense_type, amount, notes, created_at, updated_at FROM monthly_variable_expenses WHERE 1=1';
        $params = [];
        if ($monthCycle !== '') {
            $sql .= ' AND month_cycle=?';
            $params[] = $monthCycle;
        }
        if ($expenseType !== '') {
            $sql .= ' AND expense_type=?';
            $params[] = $expenseType;
        }
        $sql .= ' ORDER BY month_cycle DESC, expense_type';

        $rows = DB::select($sql, $params);
        return ['status' => 'ok', 'rows' => $rows];
    }

    public function monthlyVariableUpsert(array $payload): array
    {
        $monthCycle = trim((string)($payload['month_cycle'] ?? ''));

        // Flask-parity single-row mode
        $expenseType = strtoupper(trim((string)($payload['expense_type'] ?? '')));
        if ($expenseType !== '') {
            $amount = (float)($payload['amount'] ?? 0);
            if ($amount < 0) {
                return ['_http' => 400, 'status' => 'error', 'error' => 'amount cannot be negative'];
            }

            DB::statement(
                'INSERT INTO monthly_variable_expenses(month_cycle, expense_type, amount, notes)
                 VALUES(?,?,?,?)
                 ON CONFLICT(month_cycle, expense_type)
                 DO UPDATE SET amount=excluded.amount, notes=excluded.notes, updated_at=CURRENT_TIMESTAMP',
                [$monthCycle, $expenseType, $amount, trim((string)($payload['notes'] ?? '')) ?: null]
            );

            return ['status' => 'ok', 'month_cycle' => $monthCycle, 'expense_type' => $expenseType, 'amount' => $amount];
        }

        // Back-compat bulk mode retained for existing UI draft forms
        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];
        DB::statement('DELETE FROM expenses_monthly_variable WHERE month_cycle=?', [$monthCycle]);
        $count = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $code = trim((string)($row['expense_code'] ?? ''));
            $head = trim((string)($row['expense_head'] ?? ''));
            if ($code === '' && $head === '') {
                continue;
            }

            DB::statement(
                'INSERT INTO expenses_monthly_variable(month_cycle, expense_code, expense_head, amount, notes, created_at, updated_at) VALUES(?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)',
                [$monthCycle, $code !== '' ? $code : $head, $head !== '' ? $head : $code, (float)($row['amount'] ?? 0), (string)($row['notes'] ?? '')]
            );
            $count++;
        }

        return ['status' => 'ok', 'month_cycle' => $monthCycle, 'upserted' => true, 'rows_count' => $count];
    }

    public function monthOpen(array $payload): array
    {
        try {
            DB::statement('INSERT INTO util_month_cycle(month_cycle, state) VALUES(?, ?) ON CONFLICT(month_cycle) DO UPDATE SET state=excluded.state', [$payload['month_cycle'], 'OPEN']);
        } catch (\Throwable) {
            // shell parity
        }

        return ['status' => 'ok', 'month_cycle' => $payload['month_cycle'], 'state' => 'OPEN'];
    }

    public function monthTransition(array $payload): array
    {
        $allowed = ['OPEN', 'INGEST', 'VALIDATION', 'APPROVAL', 'LOCKED'];
        if (!in_array($payload['to_state'], $allowed, true)) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'invalid transition target'];
        }

        try {
            DB::statement('INSERT INTO util_month_cycle(month_cycle, state) VALUES(?, ?) ON CONFLICT(month_cycle) DO UPDATE SET state=excluded.state', [$payload['month_cycle'], $payload['to_state']]);
        } catch (\Throwable) {
            // shell parity
        }

        return ['status' => 'ok', 'month_cycle' => $payload['month_cycle'], 'state' => $payload['to_state']];
    }
}
