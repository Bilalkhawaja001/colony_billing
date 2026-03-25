<?php

namespace Tests\Feature;

use Tests\TestCase;

class MonthGuardShellTest extends TestCase
{
    public function test_blocked_write_on_locked_month(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true,
        ]);

        $res = $this->post('/month/open');
        $res->assertStatus(423)->assertJsonPath('error', 'month locked');
    }

    public function test_allowed_write_on_unlocked_month(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->post('/month/open');
        $res->assertOk()->assertJsonPath('status', 'ok');
    }

    public function test_exception_path_allowed_when_configured(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true,
        ]);

        $res = $this->post('/month/transition');
        $res->assertOk()->assertJsonPath('mode', 'guard-shell-exception-pass');
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
            'month_guard_locked' => false,
        ]);

        $res = $this->post('/month/open');
        $res->assertStatus(403);
    }
}
