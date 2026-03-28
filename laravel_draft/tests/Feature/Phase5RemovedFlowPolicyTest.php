<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase5RemovedFlowPolicyTest extends TestCase
{
    public function test_removed_non_ev1_flows_are_hard_locked_to_410(): void
    {
        $this->withSession([
            'user_id' => 7201,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true,
        ]);

        $this->postJson('/billing/approve', ['run_id' => 1])->assertStatus(410);
        $this->postJson('/billing/adjustments/create', ['month_cycle' => '03-2026'])->assertStatus(410);
        $this->postJson('/billing/adjustments/approve', ['adjustment_id' => 1])->assertStatus(410);
        $this->postJson('/recovery/payment', ['month_cycle' => '03-2026', 'employee_id' => 'E1', 'amount_paid' => 10])->assertStatus(410);
    }
}
