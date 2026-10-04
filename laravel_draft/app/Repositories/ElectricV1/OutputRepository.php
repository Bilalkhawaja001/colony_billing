<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class OutputRepository extends BaseRepository
{
    public function replaceCycleOutputs(string $cycleStart, string $cycleEnd, array $finalRows, array $drillRows): void
    {
        DB::delete('DELETE FROM electric_v1_output_employee_final WHERE cycle_start_date=? AND cycle_end_date=?', [$cycleStart, $cycleEnd]);
        DB::delete('DELETE FROM electric_v1_output_employee_unit_drilldown WHERE cycle_start_date=? AND cycle_end_date=?', [$cycleStart, $cycleEnd]);

        foreach ($finalRows as $r) {
            DB::insert('INSERT INTO electric_v1_output_employee_final(cycle_start_date,cycle_end_date,run_id,company_id,name,total_net_billable_units,flat_rate,final_amount_before_rounding,final_amount_rounded,has_estimated_units) VALUES(?,?,?,?,?,?,?,?,?,?)', [
                $r['cycle_start_date'],$r['cycle_end_date'],$r['run_id'],$r['company_id'],$r['name'],$r['total_net_billable_units'],$r['flat_rate'],$r['final_amount_before_rounding'],$r['final_amount_rounded'],$r['has_estimated_units']
            ]);
        }

        /*
         * Full reporting payload.
         * OrchestrationService already calculates these values; they must
         * also be persisted for Excel/reporting.
         *
         * Column filtering keeps this backward-compatible if an optional
         * reporting column does not exist on an older database.
         */
        $drillColumns = array_flip(
            \Illuminate\Support\Facades\Schema::getColumnListing(
                'electric_v1_output_employee_unit_drilldown'
            )
        );

        foreach ($drillRows as $r) {
            $payload = [
                'cycle_start_date' => $r['cycle_start_date'],
                'cycle_end_date' => $r['cycle_end_date'],
                'run_id' => $r['run_id'],
                'company_id' => $r['company_id'],

                'name' => $r['name'] ?? $r['company_id'],
                'month_cycle' => $r['month_cycle'] ?? null,

                'unit_id' => $r['unit_id'],
                'room_no' => $r['room_no'] ?? '',
                'residence_type' => $r['residence_type'] ?? 'ROOM',
                'room_persons' => $r['room_persons'] ?? 0,

                'active_days' => $r['active_days'] ?? 0,
                'month_days' => $r['month_days'] ?? 0,
                'employee_attendance_in_unit' => $r['employee_attendance_in_unit'] ?? 0,

                'gross_units' => $r['gross_units'] ?? 0,
                'free_allowance_units' => $r['free_allowance_units'] ?? 0,

                'room_free_units' => $r['room_free_units'] ?? 0,
                'emp_used_units' => $r['emp_used_units'] ?? 0,
                'eligible_units' => $r['eligible_units'] ?? 0,

                'unit_used_elec' => $r['unit_used_elec'] ?? 0,
                'unit_total_attendance' => $r['unit_total_attendance'] ?? 0,

                'net_units_before_adj' => $r['net_units_before_adj'] ?? 0,
                'adjustment_units' => $r['adjustment_units'] ?? 0,
                'net_units_after_adj' => $r['net_units_after_adj'] ?? 0,

                'amount_before_rounding' => $r['amount_before_rounding'] ?? 0,

                'billable_units' => $r['billable_units']
                    ?? $r['net_units_before_adj']
                    ?? 0,

                'rate' => $r['rate'] ?? 0,
                'amount' => $r['amount'] ?? 0,

                'is_estimated' => $r['is_estimated'] ?? 'N',
                'estimate_source_cycle1' => $r['estimate_source_cycle1'] ?? null,
                'estimate_source_cycle2' => $r['estimate_source_cycle2'] ?? null,
                'estimate_source_cycle3' => $r['estimate_source_cycle3'] ?? null,
                'estimated_from_valid_cycle_count' => $r['estimated_from_valid_cycle_count'] ?? 0,
            ];

            $payload = array_intersect_key($payload, $drillColumns);

            DB::table('electric_v1_output_employee_unit_drilldown')
                ->insert($payload);
        }
    }
}
