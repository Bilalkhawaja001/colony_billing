<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BillingRunRatesParityTest extends TestCase
{
    public function test_rates_upsert_and_approve_routes_are_active(): void
    {
        $this->withSession([
            'user_id' => 401,
            'actor_user_id' => 401,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->times(2)->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('statement')->once()->andReturn(true);
        DB::shouldReceive('update')->once()->andReturn(1);

        $this->postJson('/rates/upsert', [
            'month_cycle' => '2026-03',
            'elec_rate' => 50,
            'water_general_rate' => 0.2,
            'water_drinking_rate' => 0.3,
            'school_van_rate' => 4500,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->postJson('/rates/approve', [
            'month_cycle' => '2026-03',
            'actor_user_id' => 401,
        ])->assertOk()->assertJsonPath('status', 'ok');
    }

    public function test_billing_run_contract_returns_run_id_and_run_key(): void
    {
        $this->withSession([
            'user_id' => 402,
            'actor_user_id' => 402,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('statement')->times(5)->andReturn(true);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 123]);

        $res = $this->postJson('/billing/run', [
            'month_cycle' => '2026-03',
            'run_key' => 'IDEMP-1',
            'actor_user_id' => 402,
        ]);

        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('run_id', 123)
            ->assertJsonPath('run_key', 'IDEMP-1');
    }
}
