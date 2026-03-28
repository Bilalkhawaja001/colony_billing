<?php

namespace App\Services\ElectricV1;

use Illuminate\Support\Facades\DB;

class ReadService
{
    public function bundle(string $cycleStart, string $cycleEnd, ?string $runId = null): array
    {
        $params = [$cycleStart, $cycleEnd];
        $runSql = '';
        if ($runId) { $runSql = ' AND run_id=?'; $params[] = $runId; }

        $final = DB::select('SELECT * FROM electric_v1_output_employee_final WHERE cycle_start_date=? AND cycle_end_date=?'.$runSql.' ORDER BY company_id', $params);
        $drill = DB::select('SELECT * FROM electric_v1_output_employee_unit_drilldown WHERE cycle_start_date=? AND cycle_end_date=?'.$runSql.' ORDER BY company_id, unit_id', $params);
        $exc = DB::select('SELECT * FROM electric_v1_exception_log WHERE cycle_start_date=? AND cycle_end_date=?'.$runSql.' ORDER BY logged_at, exception_code', $params);
        $hist = DB::select('SELECT * FROM electric_v1_run_history WHERE cycle_start_date=? AND cycle_end_date=? ORDER BY run_start, run_id', [$cycleStart, $cycleEnd]);

        return [
            'cycle_start_date' => $cycleStart,
            'cycle_end_date' => $cycleEnd,
            'run_id' => $runId,
            'final_outputs' => array_map(fn($r)=>(array)$r, $final),
            'drilldown_outputs' => array_map(fn($r)=>(array)$r, $drill),
            'exceptions' => array_map(fn($r)=>(array)$r, $exc),
            'run_history' => array_map(fn($r)=>(array)$r, $hist),
        ];
    }
}
