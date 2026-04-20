<?php

namespace Tests\Feature\ElectricV1;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ElectricV1RerunReplaceTest extends TestCase
{
    public function test_rerun_replaces_outputs_and_history_increments(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);

        $rows = [["unit_id"=>"U-R1","free_electric"=>10,"unit_name"=>"UR1","residence_type"=>"ROOM"]];
        $this->postJson('/api/electric-v1/input/allowance/upsert', ['rows'=>$rows])->assertOk();
        $this->postJson('/api/electric-v1/input/readings/upsert', ['rows'=>[["cycle_start_date"=>"2026-04-01","cycle_end_date"=>"2026-04-30","unit_id"=>"U-R1","previous_reading"=>100,"current_reading"=>160,"reading_status"=>"NORMAL"]]])->assertOk();
        $this->postJson('/api/electric-v1/input/attendance/upsert', ['rows'=>[["cycle_start_date"=>"2026-04-01","cycle_end_date"=>"2026-04-30","company_id"=>"E-R1","attendance_days"=>20]]])->assertOk();
        $this->postJson('/api/electric-v1/input/occupancy/upsert', ['rows'=>[["company_id"=>"E-R1","unit_id"=>"U-R1","room_id"=>"R1","from_date"=>"2026-04-01","to_date"=>"2026-04-30"]]])->assertOk();
        $this->postJson('/api/electric-v1/input/adjustments/upsert', ['rows'=>[["cycle_start_date"=>"2026-04-01","cycle_end_date"=>"2026-04-30","company_id"=>"E-R1","unit_id"=>"U-R1","adjustment_units"=>0]]])->assertOk();

        $this->postJson('/api/electric-v1/run', ['billing_month_date'=>'2026-04-01','cycle_start'=>'2026-04-01','cycle_end'=>'2026-04-30','flat_rate'=>2.0])->assertOk();
        $this->postJson('/api/electric-v1/run', ['billing_month_date'=>'2026-04-01','cycle_start'=>'2026-04-01','cycle_end'=>'2026-04-30','flat_rate'=>2.0])->assertOk();

        $finalCount = DB::table('electric_v1_output_employee_final')->where('cycle_start_date','2026-04-01')->where('cycle_end_date','2026-04-30')->count();
        $historyCount = DB::table('electric_v1_run_history')->where('cycle_start_date','2026-04-01')->where('cycle_end_date','2026-04-30')->count();

        $this->assertGreaterThanOrEqual(1, $finalCount);
        $this->assertGreaterThanOrEqual(2, $historyCount);
    }
}
