<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BillingFoundationShellTest extends TestCase
{
    public function test_valid_precheck_request_reaches_real_precheck_shape(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('select')->andReturn([], [], [], []);

        $res = $this->postJson('/api/billing/precheck', ['month_cycle' => '03-2026']);
        $res->assertOk()
            ->assertJsonStructure(['status', 'stop', 'logs', 'rows_preview'])
            ->assertJsonPath('stop', false);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);

        $res = $this->postJson('/api/billing/precheck', ['month_cycle' => '2026-03']);
        $res->assertStatus(422);
    }

    public function test_unauthenticated_blocked(): void
    {
        $res = $this->postJson('/api/billing/precheck', ['month_cycle' => '03-2026']);
        $res->assertStatus(401);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);

        $res = $this->postJson('/api/billing/precheck', ['month_cycle' => '03-2026']);
        $res->assertStatus(403);
    }

    public function test_locked_month_blocked_when_not_exception(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'LOCKED']);

        $res = $this->postJson('/api/billing/precheck', ['month_cycle' => '03-2026']);
        $res->assertStatus(409)->assertJsonPath('guard', 'month.guard.domain');
    }

    public function test_response_shape_parity_keys_present(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('select')->andReturn([], [], [], []);

        $res = $this->postJson('/api/billing/precheck', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonStructure([
            'status',
            'stop',
            'logs',
            'rows_preview',
        ]);
    }
}
