<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BillingLockApproveFlowTest extends TestCase
{
    public function test_valid_lock_request(): void
    {
        $this->withSession([
            'user_id' => 90,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true, // lock is exception path
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 7, 'month_cycle' => '03-2026', 'run_status' => 'APPROVED']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'APPROVAL']);
        DB::shouldReceive('update')->once()->andReturn(1);

        $res = $this->postJson('/billing/lock', ['run_id' => 7]);
        $res->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('run_status', 'LOCKED');
    }

    public function test_approve_flow_is_intentionally_removed_with_410(): void
    {
        $this->withSession([
            'user_id' => 91,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true, // exception path in guard, but service still returns 410 by policy
        ]);

        $res = $this->postJson('/billing/approve', ['run_id' => 77]);
        $res->assertStatus(410)->assertJsonPath('status', 'error');
    }

    public function test_invalid_input_for_lock(): void
    {
        $this->withSession([
            'user_id' => 92,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->postJson('/billing/lock', []);
        $res->assertStatus(422);
    }

    public function test_unauthenticated_blocked(): void
    {
        $this->postJson('/billing/lock', ['run_id' => 1])->assertStatus(401);
        $this->postJson('/billing/approve', [])->assertStatus(401);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 93,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $this->postJson('/billing/lock', ['run_id' => 1])->assertStatus(403);
        $this->postJson('/billing/approve', [])->assertStatus(403);
    }

    public function test_locked_month_behavior_as_proven_exception_paths(): void
    {
        $this->withSession([
            'user_id' => 94,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 8, 'month_cycle' => '03-2026', 'run_status' => 'APPROVED']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'APPROVAL']);
        DB::shouldReceive('update')->once()->andReturn(1);

        $this->postJson('/billing/lock', ['run_id' => 8])->assertStatus(200);
        $this->postJson('/billing/approve', ['run_id' => 8])->assertStatus(410);
    }

    public function test_state_transition_result_keys_present(): void
    {
        $this->withSession([
            'user_id' => 95,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 9, 'month_cycle' => '03-2026', 'run_status' => 'APPROVED']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'APPROVAL']);
        DB::shouldReceive('update')->once()->andReturn(1);

        $res = $this->postJson('/billing/lock', ['run_id' => 9]);
        $res->assertOk()->assertJsonStructure(['status', 'run_id', 'run_status']);
    }
}
