<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BillingParitySubsetEndpointsTest extends TestCase
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

    public function test_billing_elec_compute_endpoint_is_active(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('select')->twice()->andReturn([], []);

        $res = $this->postJson('/billing/elec/compute', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('month_cycle', '03-2026');
    }

    public function test_billing_water_compute_endpoint_returns_rows(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 91, 'run_status' => 'LOCKED']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E-1', 'water_amount' => 150.50],
        ]);

        $res = $this->postJson('/billing/water/compute', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonPath('billing_run_id', 91)->assertJsonPath('rows.0.employee_id', 'E-1');
    }

    public function test_billing_run_maps_to_finalize_chain(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });
        DB::shouldReceive('delete')->times(2)->andReturn(0, 0);
        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('insert')->times(2)->andReturnTrue();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 701]);
        DB::shouldReceive('statement')->once()->andReturnTrue();

        $res = $this->postJson('/billing/run', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('run_id', 701);
    }

    public function test_billing_fingerprint_reads_latest_run_key(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 702, 'run_key' => 'fp_abc', 'run_status' => 'LOCKED']);

        $res = $this->postJson('/billing/fingerprint', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonPath('fingerprint', 'fp_abc')->assertJsonPath('billing_run_id', 702);
    }

    public function test_billing_adjustments_list_no_longer_410_placeholder(): void
    {
        $this->actingBillingAdmin();

        $res = $this->getJson('/billing/adjustments/list?month_cycle=03-2026');
        $res->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('rows', []);
    }

    public function test_billing_print_employee_endpoint_returns_lines(): void
    {
        $this->actingBillingAdmin();

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 703, 'run_status' => 'LOCKED']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['utility_type' => 'ELEC', 'qty' => 1.0, 'amount' => 455.25, 'source_ref' => 'U-1'],
        ]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['total_amount' => 455.25]);

        $res = $this->getJson('/billing/print/03-2026/E-1');
        $res->assertOk()
            ->assertJsonPath('month_cycle', '03-2026')
            ->assertJsonPath('employee_id', 'E-1')
            ->assertJsonPath('rows.0.utility_type', 'ELEC')
            ->assertJsonPath('total_amount', 455.25);
    }

    public function test_existing_removed_adjustment_create_stays_410(): void
    {
        $this->actingBillingAdmin();

        $res = $this->postJson('/billing/adjustments/create', ['month_cycle' => '03-2026']);
        $res->assertStatus(410);
    }
}
