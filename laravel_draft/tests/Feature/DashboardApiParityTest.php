<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardApiParityTest extends TestCase
{
    public function test_colony_kpis_api_returns_expected_shape(): void
    {
        $this->withSession(['user_id' => 10, 'role' => 'VIEWER', 'force_change_password' => 0]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['month_cycle' => '03-2026']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['employees_billed' => 2, 'total_billed' => 500.50]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['family_members' => 3]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['van_kids' => 1]);

        $res = $this->getJson('/api/dashboard/colony-kpis');
        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('month_cycle', '03-2026')
            ->assertJsonPath('kpis.employees_billed', 2)
            ->assertJsonPath('kpis.van_kids', 1);
    }

    public function test_family_members_api_returns_rows(): void
    {
        $this->withSession(['user_id' => 10, 'role' => 'VIEWER', 'force_change_password' => 0]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['month_cycle' => '03-2026']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E001', 'family_member_name' => 'Kid 1', 'relation' => 'Child', 'age' => 7],
        ]);

        $res = $this->getJson('/api/dashboard/family-members');
        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('rows.0.employee_id', 'E001');
    }

    public function test_van_kids_api_returns_rows(): void
    {
        $this->withSession(['user_id' => 10, 'role' => 'VIEWER', 'force_change_password' => 0]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['month_cycle' => '03-2026']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E002', 'child_name' => 'Kid 2', 'school_name' => 'City School', 'class_level' => '2', 'amount' => 1000],
        ]);

        $res = $this->getJson('/api/dashboard/van-kids');
        $res->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('rows.0.child_name', 'Kid 2');
    }
}
