<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InfraEndpointsParityTest extends TestCase
{
    private function asRole(string $role): void
    {
        $this->withSession([
            'user_id' => 88,
            'role' => $role,
            'force_change_password' => 0,
        ]);
    }

    public function test_health_endpoint_is_public_and_ok(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('service', 'laravel-draft');
    }

    public function test_root_redirects_to_login_or_dashboard_by_session(): void
    {
        $this->get('/')->assertRedirect('/login');

        $this->withSession(['user_id' => 5]);
        $this->get('/')->assertRedirect('/ui/dashboard');
    }

    public function test_rates_upsert_and_approve_alias_endpoints_are_active(): void
    {
        $this->asRole('BILLING_ADMIN');

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('statement')->once()->andReturn(true);
        $this->postJson('/rates/upsert', [
            'month_cycle' => '03-2026',
            'rates' => [
                ['utility_type' => 'ELEC', 'rate' => 23.0],
            ],
        ])->assertOk()->assertJsonPath('upserted', true);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['state' => 'OPEN']);
        DB::shouldReceive('statement')->once()->andReturn(true);
        $this->postJson('/rates/approve', ['month_cycle' => '03-2026'])
            ->assertOk()
            ->assertJsonPath('approved', true)
            ->assertJsonPath('state', 'APPROVAL');
    }

    public function test_expenses_monthly_variable_get_and_upsert_shapes(): void
    {
        $this->asRole('VIEWER');

        DB::shouldReceive('select')->once()->andReturn([
            (object) ['month_cycle' => '03-2026', 'expense_code' => 'GEN', 'expense_head' => 'Generator', 'amount' => 1000.0, 'notes' => 'fuel'],
        ]);

        $this->getJson('/expenses/monthly-variable?month_cycle=03-2026')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('rows.0.expense_code', 'GEN');

        $this->asRole('DATA_ENTRY');
        DB::shouldReceive('statement')->times(3)->andReturn(true);

        $this->postJson('/expenses/monthly-variable/upsert', [
            'month_cycle' => '03-2026',
            'rows' => [
                ['expense_code' => 'GEN', 'expense_head' => 'Generator', 'amount' => 1100],
                ['expense_code' => 'SEC', 'expense_head' => 'Security', 'amount' => 500],
            ],
        ])->assertOk()
            ->assertJsonPath('upserted', true)
            ->assertJsonPath('rows_count', 2);
    }

    public function test_imports_error_report_blank_token_returns_not_found_shape(): void
    {
        $this->asRole('VIEWER');

        $this->getJson('/imports/error-report/%20')
            ->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_registry_employee_get_endpoint_remains_active(): void
    {
        $this->asRole('VIEWER');

        DB::shouldReceive('selectOne')->once()->andReturn((object) [
            'company_id' => 'EMP-1',
            'name' => 'Ali',
            'active' => 'Yes',
            'department' => 'Ops',
            'designation' => 'Staff',
            'unit_id' => 'U-1',
            'colony_type' => 'Family',
            'block_floor' => 'B1',
            'room_no' => '01',
        ]);

        $this->getJson('/registry/employees/EMP-1')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('row.company_id', 'EMP-1');
    }
}
