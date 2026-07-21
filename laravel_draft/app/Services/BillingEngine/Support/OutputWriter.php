<?php
namespace App\Services\BillingEngine\Support;

use Illuminate\Support\Facades\DB;

class OutputWriter
{
    /**
     * Engine rows ko output tables me likhta hai (cycle-scoped replace).
     * @return array ['final_rows'=>int,'drill_rows'=>int,'run_id'=>string]
     */
    public function write(array $preview, string $runId): array
    {
        $cs = $preview['cycle_start'];
        $ce = $preview['cycle_end'];
        $rate = (float) $preview['rate'];
        $rows = $preview['rows'] ?? [];

        // employee names
        $names = DB::table('employees_master')->pluck('name', 'company_id');

        // employee-wise total (ek employee kai rooms me ho sakta hai)
        $byEmp = [];
        foreach ($rows as $r) {
            $cid = (string) $r['company_id'];
            if (!isset($byEmp[$cid])) $byEmp[$cid] = ['units'=>0.0, 'amount'=>0.0];
            $byEmp[$cid]['units']  += (float) $r['billable_units'];
            $byEmp[$cid]['amount'] += (float) $r['amount'];
        }

        $finalCount = 0; $drillCount = 0;

        DB::transaction(function () use ($cs,$ce,$rate,$rows,$byEmp,$names,$runId,&$finalCount,&$drillCount) {
            DB::table('electric_v1_output_employee_final')
                ->where('cycle_start_date',$cs)->where('cycle_end_date',$ce)->delete();
            DB::table('electric_v1_output_employee_unit_drilldown')
                ->where('cycle_start_date',$cs)->where('cycle_end_date',$ce)->delete();

            foreach ($byEmp as $cid => $t) {
                DB::table('electric_v1_output_employee_final')->insert([
                    'cycle_start_date' => $cs,
                    'cycle_end_date'   => $ce,
                    'run_id'           => $runId,
                    'company_id'       => $cid,
                    'name'             => $names[$cid] ?? 'Unknown',
                    'total_net_billable_units' => round($t['units'],4),
                    'flat_rate'        => $rate,
                    'final_amount_before_rounding' => round($t['amount'],4),
                    'final_amount_rounded'         => round($t['amount'],0),
                    'has_estimated_units' => 'N',
                ]);
                $finalCount++;
            }

            foreach ($rows as $r) {
                DB::table('electric_v1_output_employee_unit_drilldown')->insert([
                    'cycle_start_date' => $cs,
                    'cycle_end_date'   => $ce,
                    'run_id'           => $runId,
                    'company_id'       => (string) $r['company_id'],
                    'unit_id'          => $r['unit_id'],
                    'room_no'          => $r['room_no'] ?? null,
                    'residence_type'   => 'ROOM',
                    'employee_attendance_in_unit' => 0,
                    'gross_units'      => (float) ($r['room_units'] ?? 0),
                    'free_allowance_units' => (float) ($r['allowance'] ?? 0),
                    'net_units_before_adj' => (float) $r['billable_units'],
                    'adjustment_units' => 0,
                    'net_units_after_adj' => (float) $r['billable_units'],
                    'amount_before_rounding' => (float) $r['amount'],
                    'active_days'      => 0,
                    'amount'           => (float) $r['amount'],
                    'rate'             => $rate,
                    'billable_units'   => (float) $r['billable_units'],
                    'is_estimated'     => 'N',
                ]);
                $drillCount++;
            }
        });

        return ['final_rows'=>$finalCount, 'drill_rows'=>$drillCount, 'run_id'=>$runId];
    }
}
