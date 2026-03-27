<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImportsMonthlyRatesParityTest extends TestCase
{
    private function asBillingAdmin(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    public function test_imports_ingest_preview_success_shape(): void
    {
        $this->asBillingAdmin();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);

        $this->postJson('/imports/meter-register/ingest-preview', ['month_cycle' => '03-2026', 'rows_received' => 20])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['token', 'preview' => ['rows_received', 'estimated_errors']]);
    }

    public function test_imports_mark_validated_requires_token(): void
    {
        $this->asBillingAdmin();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);

        $this->postJson('/imports/mark-validated', ['month_cycle' => '03-2026'])
            ->assertStatus(422);
    }

    public function test_unit_aliases_and_error_report_read_shapes(): void
    {
        $this->asBillingAdmin();
        DB::shouldReceive('select')->times(2)->andReturn([], []);

        $this->getJson('/imports/unit-id-aliases')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['rows']);

        $this->getJson('/imports/error-report/tok_1')
            ->assertOk()
            ->assertJsonPath('token', 'tok_1')
            ->assertJsonStructure(['rows']);
    }

    public function test_monthly_rates_initialize_and_upsert_are_guarded_by_month_lock(): void
    {
        $this->asBillingAdmin();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'LOCKED']);

        $this->postJson('/monthly-rates/initialize', ['month_cycle' => '03-2026'])
            ->assertStatus(409)
            ->assertJsonPath('guard', 'month.guard.domain');
    }

    public function test_monthly_rates_config_and_history_and_upsert_success(): void
    {
        $this->asBillingAdmin();

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('statement')->once()->andReturn(true);
        $this->postJson('/monthly-rates/config/upsert', [
            'month_cycle' => '03-2026',
            'rates' => [
                ['utility_type' => 'ELEC', 'rate' => 26.5],
            ],
        ])->assertOk()->assertJsonPath('upserted', true);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['config_json' => '{"rates":[{"utility_type":"ELEC","rate":26.5}]}']);
        $this->getJson('/monthly-rates/config?month_cycle=03-2026')
            ->assertOk()
            ->assertJsonPath('month_cycle', '03-2026')
            ->assertJsonStructure(['config' => ['rates']]);

        DB::shouldReceive('select')->once()->andReturn([]);
        $this->getJson('/monthly-rates/history')
            ->assertOk()
            ->assertJsonStructure(['rows']);
    }

    public function test_month_open_and_transition_success(): void
    {
        $this->asBillingAdmin();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('statement')->once()->andReturn(true);

        $this->postJson('/month/open', ['month_cycle' => '03-2026'])
            ->assertOk()
            ->assertJsonPath('state', 'OPEN');

        DB::shouldReceive('statement')->once()->andReturn(true);
        $this->postJson('/month/transition', ['month_cycle' => '03-2026', 'to_state' => 'APPROVAL'])
            ->assertOk()
            ->assertJsonPath('state', 'APPROVAL');
    }
}
