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

    public function test_billing_run_requires_existing_unlocked_month_cycle(): void
    {
        $this->withSession([
            'user_id' => 402,
            'actor_user_id' => 402,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        $this->postJson('/billing/run', [
            'month_cycle' => '2026-03',
            'run_key' => 'MISSING-MONTH',
        ])->assertStatus(409);

        DB::statement("INSERT INTO util_month_cycle(month_cycle, state) VALUES('03-2026', 'LOCKED')");

        $this->postJson('/billing/run', [
            'month_cycle' => '2026-03',
            'run_key' => 'LOCKED-MONTH',
        ])->assertStatus(409);
    }

    public function test_billing_run_contract_returns_run_id_and_run_key(): void
    {
        $this->withSession([
            'user_id' => 402,
            'actor_user_id' => 402,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::statement("INSERT INTO util_month_cycle(month_cycle, state) VALUES('03-2026', 'OPEN')");

        $res = $this->postJson('/billing/run', [
            'month_cycle' => '2026-03',
            'run_key' => 'IDEMP-1',
            'actor_user_id' => 402,
        ]);

        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('run_key', 'IDEMP-1');

        $this->assertGreaterThan(0, (int) $res->json('run_id'));
    }
}
