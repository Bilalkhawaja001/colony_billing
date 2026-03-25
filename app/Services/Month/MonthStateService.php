<?php

namespace App\Services\Month;

use Illuminate\Support\Facades\DB;

class MonthStateService
{
    public function state(string $monthCycle): ?string
    {
        $row = DB::selectOne('SELECT state FROM util_month_cycle WHERE month_cycle=?', [$monthCycle]);
        return $row ? (string)$row->state : null;
    }

    public function isLocked(string $monthCycle): bool
    {
        return strtoupper((string)$this->state($monthCycle)) === 'LOCKED';
    }

    public function monthFromRunId(int $runId): ?string
    {
        $row = DB::selectOne('SELECT month_cycle FROM util_billing_run WHERE id=?', [$runId]);
        return $row ? (string)$row->month_cycle : null;
    }
}
