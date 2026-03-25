<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdjustmentRecoveryReconciliationTest extends TestCase
{
    public function test_valid_adjustment_create(): void
    {
        $this->withSession([
            'user_id' => 201,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->postJson('/billing/adjustments/create', [
            'month_cycle' => '03-2026',
            'employee_id' => 'E-100',
            'utility_type' => 'ELEC',
            'reason' => 'test',
            'amount_delta' => 10,
        ]);

        $res->assertStatus(410)->assertJsonPath('error', 'deduction/adjustment flow removed; billing generation only');
    }

    public function test_valid_adjustment_approve(): void
    {
        $this->withSession([
            'user_id' => 202,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->postJson('/billing/adjustments/approve', []);
        $res->assertStatus(410)->assertJsonPath('error', 'adjustment approvals removed');
    }

    public function test_invalid_input(): void
    {
        $this->withSession([
            'user_id' => 203,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        // month_cycle regex invalid in request validation
        $this->postJson('/billing/adjustments/create', ['month_cycle' => '2026-03'])->assertStatus(422);
        // reconciliation requires month_cycle
        $this->getJson('/reports/reconciliation')->assertStatus(422);
    }

    public function test_unauthenticated_blocked(): void
    {
        $this->postJson('/billing/adjustments/create', [])->assertStatus(401);
        $this->postJson('/recovery/payment', [])->assertStatus(401);
        $this->getJson('/reports/reconciliation?month_cycle=03-2026')->assertStatus(401);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 204,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $this->postJson('/billing/adjustments/create', [])->assertStatus(403);
        $this->postJson('/recovery/payment', [])->assertStatus(403);
        $this->getJson('/reports/reconciliation?month_cycle=03-2026')->assertStatus(403);
    }

    public function test_locked_month_behavior_as_proven(): void
    {
        $this->withSession([
            'user_id' => 205,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => true,
        ]);

        // Proven Flask behavior: these endpoints currently disabled with 410.
        $this->postJson('/billing/adjustments/create', [])->assertStatus(410);
        $this->postJson('/billing/adjustments/approve', [])->assertStatus(410);
        $this->postJson('/recovery/payment', [])->assertStatus(410);
    }

    public function test_valid_recovery_payment(): void
    {
        $this->withSession([
            'user_id' => 206,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        $res = $this->postJson('/recovery/payment', [
            'employee_id' => 'E-100',
            'month_cycle' => '03-2026',
            'amount_paid' => 250,
        ]);

        $res->assertStatus(410)->assertJsonPath('error', 'payment receiving disabled; billing generation only');
    }

    public function test_reconciliation_response_parity_where_proven(): void
    {
        $this->withSession([
            'user_id' => 207,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);

        // reporting_run_id lookup
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 301, 'run_status' => 'LOCKED']);
        // billed total
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['billed_total' => 1000.00]);
        // billed by utility
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['utility_type' => 'ELEC', 'billed_amount' => 700.00],
            (object) ['utility_type' => 'WATER', 'billed_amount' => 300.00],
        ]);
        // billed by employee
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E-1', 'billed_amount' => 400.00],
            (object) ['employee_id' => 'E-2', 'billed_amount' => 600.00],
        ]);
        // recovery total
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['recovered_total' => 250.00]);
        // recovery by employee
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E-1', 'recovered_amount' => 100.00],
            (object) ['employee_id' => 'E-2', 'recovered_amount' => 150.00],
        ]);

        $res = $this->getJson('/reports/reconciliation?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status',
            'month_cycle',
            'billing_run_id',
            'summary' => ['billed_total', 'recovered_total', 'outstanding_total', 'recovery_ratio'],
            'by_utility',
            'by_employee',
            'notes',
        ]);
    }
}
