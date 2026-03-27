<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WaterAnalyticsParityEndpointsTest extends TestCase
{
    private function actingBillingAdmin(): void
    {
        $this->withSession([
            'user_id' => 120,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);
    }

    public function test_ui_water_meters_and_van_pages_are_available(): void
    {
        $this->actingBillingAdmin();

        $this->get('/ui/water-meters')->assertOk();
        $this->get('/ui/van')->assertOk();
    }

    public function test_reports_van_shape_matches_flask_surface(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E-1', 'child_name' => 'Kid A', 'school_name' => 'ABC', 'class_level' => '2', 'amount' => 1200.00],
        ]);

        $res = $this->getJson('/reports/van?month_cycle=03-2026');
        $res->assertOk()
            ->assertJsonPath('month_cycle', '03-2026')
            ->assertJsonPath('rows.0.employee_id', 'E-1');
    }

    public function test_water_occupancy_snapshot_returns_zone_summary_and_rows(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('select')->times(5)->andReturn(
            [(object) ['unit_id' => 'WA-01', 'emp_count' => 2]],
            [(object) ['company_id' => 'E-1', 'unit_id' => 'WA-01', 'spouse_count' => 1, 'children_count' => 1]],
            [(object) ['company_id' => 'E-1', 'child_row_count' => 1]],
            [(object) ['unit_id' => 'WA-01', 'colony_type' => 'Family Colony', 'free_water_liters' => 0]],
            []
        );

        $res = $this->getJson('/api/water/occupancy-snapshot?month_cycle=03-2026');
        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('month_cycle', '03-2026')
            ->assertJsonPath('rows.0.water_zone', 'FAMILY_METER')
            ->assertJsonPath('zone_summary.FAMILY_METER.units', 1)
            ->assertJsonPath('zone_summary.FAMILY_METER.total_persons', 4);
    }

    public function test_water_zone_adjustments_get_and_post_shapes(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('select')->once()->andReturn([
            (object) [
                'month_cycle' => '03-2026',
                'water_zone' => 'FAMILY_METER',
                'raw_liters' => 1000,
                'common_use_liters' => 100,
                'reason_code' => 'Cleaning/Washing',
                'notes' => 'x',
                'source_ref' => 'MTR-1',
            ],
        ]);

        $this->getJson('/api/water/zone-adjustments?month_cycle=03-2026')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('rows.0.water_zone', 'FAMILY_METER')
            ->assertJsonPath('rows.0.billable_residential_liters', 900)
            ->assertJsonPath('allowed_reason_codes.0', 'Plants Irrigation');

        $this->postJson('/api/water/zone-adjustments', ['month_cycle' => '03-2026'])
            ->assertStatus(400)
            ->assertJsonPath('error', 'rows[] is required');

        DB::shouldReceive('statement')->once()->andReturnTrue();
        DB::shouldReceive('select')->once()->andReturn([
            (object) [
                'month_cycle' => '03-2026',
                'water_zone' => 'BACHELOR_METER',
                'raw_liters' => 400,
                'common_use_liters' => 50,
                'reason_code' => 'Overflow',
                'notes' => '',
                'source_ref' => null,
            ],
        ]);

        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => '03-2026',
            'rows' => [[
                'water_zone' => 'BACHELOR_METER',
                'raw_liters' => 400,
                'common_use_liters' => 50,
                'reason_code' => 'Overflow',
            ]],
        ])->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('rows.1.water_zone', 'BACHELOR_METER');
    }

    public function test_water_allocation_preview_returns_expected_payload_shape(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('select')->times(6)->andReturn(
            [],
            [(object) ['unit_id' => 'WA-01', 'emp_count' => 2]],
            [(object) ['company_id' => 'E-1', 'unit_id' => 'WA-01', 'spouse_count' => 1, 'children_count' => 1]],
            [],
            [(object) ['unit_id' => 'WA-01', 'colony_type' => 'Family Colony', 'free_water_liters' => 0]],
            []
        );

        $res = $this->getJson('/api/water/allocation-preview?month_cycle=03-2026');
        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('inputs.measurement_unit', 'liters')
            ->assertJsonPath('summary.units', 1)
            ->assertJsonPath('rows.0.unit_id', 'WA-01');
    }
}
