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

        foreach ($drillRows as $r) {
            DB::insert('INSERT INTO electric_v1_output_employee_unit_drilldown(cycle_start_date,cycle_end_date,run_id,company_id,unit_id,residence_type,employee_attendance_in_unit,gross_units,free_allowance_units,net_units_before_adj,adjustment_units,net_units_after_adj,amount_before_rounding,is_estimated,estimate_source_cycle1,estimate_source_cycle2,estimate_source_cycle3,estimated_from_valid_cycle_count) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                $r['cycle_start_date'],$r['cycle_end_date'],$r['run_id'],$r['company_id'],$r['unit_id'],$r['residence_type'],$r['employee_attendance_in_unit'],$r['gross_units'],$r['free_allowance_units'],$r['net_units_before_adj'],$r['adjustment_units'],$r['net_units_after_adj'],$r['amount_before_rounding'],$r['is_estimated'],$r['estimate_source_cycle1'] ?? null,$r['estimate_source_cycle2'] ?? null,$r['estimate_source_cycle3'] ?? null,$r['estimated_from_valid_cycle_count'] ?? 0
            ]);
        }
    }
}
