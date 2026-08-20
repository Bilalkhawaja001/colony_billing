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
        $monthCycle = substr((string) $ce, 0, 7);

        // employee names
        $names = DB::table('employees_master')->pluck('name', 'company_id');

        // room metadata from Unit Directory backfill
        $roomResidenceTypes = DB::table('util_unit_rooms')
            ->select('unit_id', 'room_no', 'residence_type')
            ->get()
            ->mapWithKeys(function ($row) {
                return [trim((string) $row->unit_id) . '|' . trim((string) $row->room_no) => $row->residence_type];
            });

        // employee-wise total (ek employee kai rooms me ho sakta hai)
        $byEmp = [];
        foreach ($rows as $r) {
            $cid = (string) $r['company_id'];
            if (!isset($byEmp[$cid])) $byEmp[$cid] = ['units'=>0.0, 'amount'=>0.0];
            $byEmp[$cid]['units']  += (float) $r['billable_units'];
            $byEmp[$cid]['amount'] += (float) $r['amount'];
        }

        $finalCount = 0; $drillCount = 0;

        DB::transaction(function () use ($cs,$ce,$rate,$rows,$byEmp,$names,$roomResidenceTypes,$monthCycle,$runId,&$finalCount,&$drillCount) {
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
                $cid = (string) $r['company_id'];
                $unitId = (string) $r['unit_id'];
                $roomNo = $r['room_no'] ?? null;
                $roomKey = trim($unitId) . '|' . trim((string) $roomNo);

                DB::table('electric_v1_output_employee_unit_drilldown')->insert([
                    'cycle_start_date' => $cs,
                    'cycle_end_date'   => $ce,
                    'run_id'           => $runId,
                    'company_id'       => $cid,
                    'name'             => $names[$cid] ?? 'Unknown',
                    'month_cycle'      => $monthCycle,
                    'unit_id'          => $unitId,
                    'room_no'          => $roomNo,
                    'residence_type'   => $roomResidenceTypes[$roomKey] ?? null,
                    'room_persons'     => $r['room_persons'] ?? null,
                    'active_days'      => $r['active_days'] ?? null,
                    'employee_attendance_in_unit' => $r['employee_attendance_in_unit'] ?? null,
                    'gross_units'      => $r['room_units'] ?? null,
                    'free_allowance_units' => $r['allowance'] ?? null,
                    'room_free_units'  => $r['allowance'] ?? null,
                    'emp_used_units'   => $r['emp_used_units'] ?? null,
                    'eligible_units'   => $r['eligible_units'] ?? null,
                    'unit_used_elec'   => $r['unit_used_elec'] ?? null,
                    'unit_total_attendance' => $r['unit_total_attendance'] ?? null,
                    'net_units_before_adj' => $r['billable_units'] ?? null,
                    'adjustment_units' => 0,
                    'net_units_after_adj' => $r['billable_units'] ?? null,
                    'amount_before_rounding' => $r['amount'] ?? null,
                    'amount'           => $r['amount'] ?? null,
                    'rate'             => $rate,
                    'billable_units'   => $r['billable_units'] ?? null,
                    'is_estimated'     => 'N',
                ]);
                $drillCount++;
            }
        });

        return ['final_rows'=>$finalCount, 'drill_rows'=>$drillCount, 'run_id'=>$runId];
    }
}
