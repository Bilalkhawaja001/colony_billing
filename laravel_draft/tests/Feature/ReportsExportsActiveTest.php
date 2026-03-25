<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsExportsActiveTest extends TestCase
{
    public function test_unauthenticated_blocked(): void
    {
        $this->getJson('/reports/monthly-summary?month_cycle=03-2026')->assertStatus(401);
        $this->get('/export/excel/reconciliation?month_cycle=03-2026')->assertStatus(302);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 301,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);

        $this->getJson('/reports/monthly-summary?month_cycle=03-2026')->assertStatus(403);
        $this->getJson('/reports/reconciliation?month_cycle=03-2026')->assertStatus(403);
    }

    public function test_valid_report_request(): void
    {
        $this->withSession([
            'user_id' => 302,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 11, 'run_status' => 'LOCKED']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['utility_type' => 'ELEC', 'total_amount' => 700.00, 'total_qty' => 10.0],
        ]);

        $res = $this->getJson('/reports/monthly-summary?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure(['month_cycle', 'billing_run_id', 'rows']);
    }

    public function test_valid_export_request_if_active(): void
    {
        $this->withSession([
            'user_id' => 303,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->twice()->andReturn((object) ['id' => 21, 'run_status' => 'LOCKED'], (object) ['billed_total' => 500.00]);
        DB::shouldReceive('select')->times(4)->andReturn(
            [(object) ['utility_type' => 'ELEC', 'billed_amount' => 500.00]],
            [(object) ['employee_id' => 'E-10', 'billed_amount' => 500.00]],
            [(object) ['employee_id' => 'E-10', 'recovered_amount' => 200.00]],
            []
        );

        $res = $this->get('/export/excel/reconciliation?month_cycle=03-2026');
        $res->assertOk();
        $this->assertStringContainsString('text/csv', (string)$res->headers->get('content-type'));
    }

    public function test_response_shape_parity_where_proven(): void
    {
        $this->withSession([
            'user_id' => 304,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 55, 'run_status' => 'APPROVED']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['billed_total' => 800.00]);
        DB::shouldReceive('select')->once()->andReturn([(object) ['utility_type' => 'WATER', 'billed_amount' => 300.00]]);
        DB::shouldReceive('select')->once()->andReturn([(object) ['employee_id' => 'E-1', 'billed_amount' => 800.00]]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['recovered_total' => 100.00]);
        DB::shouldReceive('select')->once()->andReturn([(object) ['employee_id' => 'E-1', 'recovered_amount' => 100.00]]);

        $res = $this->getJson('/reports/reconciliation?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status', 'month_cycle', 'billing_run_id',
            'summary' => ['billed_total', 'recovered_total', 'outstanding_total', 'recovery_ratio'],
            'by_utility', 'by_employee', 'notes'
        ]);
    }
}
