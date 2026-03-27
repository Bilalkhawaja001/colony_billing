<?php

namespace Tests\Feature;

use Tests\TestCase;

class DifferentParityExceptionsTest extends TestCase
{
    private function billingAdminSession(): void
    {
        $this->withSession([
            'user_id' => 700,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);
    }

    public function test_billing_approve_is_intentionally_removed_with_410(): void
    {
        $this->billingAdminSession();

        $this->postJson('/billing/approve', [])
            ->assertStatus(410)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error', 'approval flow removed; use direct finalize flow')
            ->assertJsonPath('parity_note', 'Flask evidence keeps /billing/approve intentionally removed');
    }

    public function test_billing_adjustment_create_is_intentionally_removed_with_410(): void
    {
        $this->billingAdminSession();

        $this->postJson('/billing/adjustments/create', [
            'month_cycle' => '03-2026',
            'employee_id' => 'E-100',
            'utility_type' => 'ELEC',
            'reason' => 'parity-proof',
            'amount_delta' => 10,
        ])
            ->assertStatus(410)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error', 'deduction/adjustment flow removed; billing generation only')
            ->assertJsonPath('parity_note', 'Flask evidence has immediate 410 for /billing/adjustments/create');
    }

    public function test_billing_adjustment_approve_is_intentionally_removed_with_410(): void
    {
        $this->billingAdminSession();

        $this->postJson('/billing/adjustments/approve', [
            'adjustment_id' => 1,
            'approved' => true,
        ])
            ->assertStatus(410)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error', 'adjustment approvals removed')
            ->assertJsonPath('parity_note', 'Flask evidence has immediate 410 for /billing/adjustments/approve');
    }

    public function test_recovery_payment_is_intentionally_removed_with_410(): void
    {
        $this->billingAdminSession();

        $this->postJson('/recovery/payment', [
            'employee_id' => 'E-100',
            'month_cycle' => '03-2026',
            'amount_paid' => 250,
        ])
            ->assertStatus(410)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error', 'payment receiving disabled; billing generation only')
            ->assertJsonPath('parity_note', 'Flask evidence has immediate 410 for /recovery/payment');
    }
}
