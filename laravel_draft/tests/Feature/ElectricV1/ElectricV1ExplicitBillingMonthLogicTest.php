<?php

namespace Tests\Feature\ElectricV1;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ElectricV1ExplicitBillingMonthLogicTest extends TestCase
{
    public function test_room_allowance_uses_billing_month_days_not_reading_cycle_days(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);

        $this->postJson('/api/electric-v1/input/allowance/upsert', [
            'rows' => [[
                'unit_id' => 'U-M1',
                'free_electric' => 31,
                'unit_name' => 'Unit M1',
                'residence_type' => 'ROOM',
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/readings/upsert', [
            'rows' => [[
                'cycle_start_date' => '2026-04-10',
                'cycle_end_date' => '2026-04-20',
                'unit_id' => 'U-M1',
                'previous_reading' => 100,
                'current_reading' => 131,
                'reading_status' => 'NORMAL',
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/attendance/upsert', [
            'rows' => [[
                'cycle_start_date' => '2026-04-10',
                'cycle_end_date' => '2026-04-20',
                'company_id' => 'E-M1',
                'attendance_days' => 11,
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/occupancy/upsert', [
            'rows' => [[
                'company_id' => 'E-M1',
                'unit_id' => 'U-M1',
                'room_id' => 'R1',
                'from_date' => '2026-04-10',
                'to_date' => '2026-04-20',
            ]],
        ])->assertOk();

        $run = $this->postJson('/api/electric-v1/run', [
            'billing_month_date' => '2026-03-01',
            'cycle_start' => '2026-04-10',
            'cycle_end' => '2026-04-20',
            'flat_rate' => 2,
        ])->assertOk()->json('data');

        $row = DB::table('electric_v1_output_employee_unit_drilldown')->where('run_id', $run['run_id'])->first();
        $this->assertNotNull($row);
        $this->assertEquals(31.0, (float)$row->gross_units);
        $this->assertEquals(11.0, (float)$row->free_allowance_units);
        $this->assertEquals(20.0, (float)$row->net_units_before_adj);
        $this->assertEquals(40.0, (float)$row->amount_before_rounding);
    }

    public function test_explicit_billing_month_changes_allowance_divisor_separately_from_reading_period(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);

        $this->postJson('/api/electric-v1/input/allowance/upsert', [
            'rows' => [[
                'unit_id' => 'U-M2',
                'free_electric' => 30,
                'unit_name' => 'Unit M2',
                'residence_type' => 'ROOM',
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/readings/upsert', [
            'rows' => [[
                'cycle_start_date' => '2026-04-10',
                'cycle_end_date' => '2026-04-20',
                'unit_id' => 'U-M2',
                'previous_reading' => 100,
                'current_reading' => 130,
                'reading_status' => 'NORMAL',
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/attendance/upsert', [
            'rows' => [[
                'cycle_start_date' => '2026-04-10',
                'cycle_end_date' => '2026-04-20',
                'company_id' => 'E-M2',
                'attendance_days' => 11,
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/occupancy/upsert', [
            'rows' => [[
                'company_id' => 'E-M2',
                'unit_id' => 'U-M2',
                'room_id' => 'R1',
                'from_date' => '2026-04-10',
                'to_date' => '2026-04-20',
            ]],
        ])->assertOk();

        $marchRun = $this->postJson('/api/electric-v1/run', [
            'billing_month_date' => '2026-03-01',
            'cycle_start' => '2026-04-10',
            'cycle_end' => '2026-04-20',
            'flat_rate' => 2,
        ])->assertOk()->json('data');

        $marchRow = DB::table('electric_v1_output_employee_unit_drilldown')->where('run_id', $marchRun['run_id'])->first();
        $this->assertEquals(10.6452, round((float)$marchRow->free_allowance_units, 4));

        $aprilRun = $this->postJson('/api/electric-v1/run', [
            'billing_month_date' => '2026-04-01',
            'cycle_start' => '2026-04-10',
            'cycle_end' => '2026-04-20',
            'flat_rate' => 2,
        ])->assertOk()->json('data');

        $aprilRow = DB::table('electric_v1_output_employee_unit_drilldown')->where('run_id', $aprilRun['run_id'])->first();
        $this->assertEquals(11.0, round((float)$aprilRow->free_allowance_units, 4));
    }
}
