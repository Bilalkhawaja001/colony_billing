<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UiParityPagesTest extends TestCase
{
    public function test_dashboard_page_renders_with_parity_data(): void
    {
        $this->withSession(['user_id' => 11, 'role' => 'BILLING_ADMIN', 'force_change_password' => 0]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['month_cycle' => '03-2026']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['employees_billed' => 12, 'total_billed' => 3456.78]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['family_members' => 20]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['van_kids' => 5]);
        DB::shouldReceive('select')->twice()->andReturn([], []);

        $res = $this->get('/ui/dashboard');
        $res->assertOk()->assertSee('Dashboard')->assertSee('03-2026');
    }

    public function test_reports_page_renders(): void
    {
        $this->withSession(['user_id' => 11, 'role' => 'VIEWER', 'force_change_password' => 0]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['month_cycle' => '03-2026']);
        DB::shouldReceive('select')->once()->andReturn([(object) ['utility_type' => 'ELEC', 'total_amount' => 1000.00]]);

        $res = $this->get('/ui/reports');
        $res->assertOk()->assertSee('Reports')->assertSee('ELEC');
    }

    public function test_reconciliation_page_renders(): void
    {
        $this->withSession(['user_id' => 11, 'role' => 'VIEWER', 'force_change_password' => 0]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['month_cycle' => '03-2026']);
        DB::shouldReceive('select')->once()->andReturn([(object) ['employee_id' => 'E-1', 'billed' => 200, 'recovered' => 50, 'outstanding' => 150]]);

        $res = $this->get('/ui/reconciliation');
        $res->assertOk()->assertSee('Reconciliation')->assertSee('E-1');
    }

    public function test_month_control_page_renders(): void
    {
        $this->withSession(['user_id' => 11, 'role' => 'BILLING_ADMIN', 'force_change_password' => 0]);

        DB::shouldReceive('select')->once()->andReturn([(object) ['month_cycle' => '03-2026', 'state' => 'OPEN', 'locked_at' => null, 'finalized_at' => null]]);

        $res = $this->get('/ui/month-control');
        $res->assertOk()->assertSee('Month Control')->assertSee('OPEN');
    }
}
