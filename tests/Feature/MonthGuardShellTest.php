<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonthGuardShellTest extends TestCase
{
    public function test_blocked_write_on_locked_month(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'LOCKED']);

        $res = $this->postJson('/month/open', ['month_cycle' => '03-2026']);
        $res->assertStatus(409)->assertJsonPath('guard', 'month.guard.domain');
    }

    public function test_allowed_write_on_unlocked_month(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);

        $res = $this->postJson('/month/open', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonPath('status', 'ok');
    }

    public function test_exception_path_allowed_when_configured(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        $res = $this->postJson('/month/transition', ['month_cycle' => '03-2026', 'to_state' => 'APPROVAL']);
        $res->assertOk()->assertJsonPath('state', 'APPROVAL');
    }

    public function test_auth_failure_happens_before_month_guard(): void
    {
        $res = $this->post('/month/open');
        $res->assertRedirect('/login');
    }

    public function test_role_failure_before_month_guard(): void
    {
        $this->withSession([
            'user_id' => 20,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);

        $res = $this->post('/month/open', ['month_cycle' => '03-2026']);
        $res->assertStatus(403);
    }
}
