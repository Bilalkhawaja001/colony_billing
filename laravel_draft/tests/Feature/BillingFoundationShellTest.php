<?php

namespace Tests\Feature;

use Tests\TestCase;

class BillingFoundationShellTest extends TestCase
{
    public function test_unauthenticated_blocked(): void
    {
        $res = $this->post('/api/billing/precheck', ['month' => '2026-03']);
        $res->assertStatus(401);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->post('/api/billing/precheck', ['month' => '2026-03']);
        $res->assertStatus(403);
    }

    public function test_locked_month_blocked(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true,
        ]);

        $res = $this->post('/api/billing/precheck', ['month' => '2026-03']);
        $res->assertStatus(423)->assertJsonPath('error', 'month locked');
    }

    public function test_unlocked_month_reaches_placeholder(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->post('/api/billing/precheck', ['month' => '2026-03']);
        $res->assertStatus(501)
            ->assertJsonPath('status', 'blocked')
            ->assertJsonPath('phase', 'LIMITED_GO')
            ->assertJsonPath('action', 'billing.precheck');
    }
}
