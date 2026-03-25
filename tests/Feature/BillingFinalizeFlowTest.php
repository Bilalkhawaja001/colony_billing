<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BillingFinalizeFlowTest extends TestCase
{
    public function test_valid_finalize_request(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(2)->andReturn(0);
        DB::shouldReceive('insert')->once()->andReturnTrue();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 501]);
        DB::shouldReceive('statement')->once()->andReturnTrue();
        DB::shouldReceive('commit')->once();

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertOk()
            ->assertJsonStructure(['status', 'run_id', 'rows'])
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('run_id', 501);
    }

    public function test_invalid_month_input(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '2026-03']);
        $res->assertStatus(422);
    }

    public function test_unauthenticated_blocked(): void
    {
        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertStatus(401);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertStatus(403);
    }

    public function test_locked_month_not_blocked_for_finalize_exception_path(): void
    {
        $this->withSession([
            'user_id' => 11,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(2)->andReturn(0);
        DB::shouldReceive('insert')->once()->andReturnTrue();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 777]);
        DB::shouldReceive('statement')->once()->andReturnTrue();
        DB::shouldReceive('commit')->once();

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertStatus(200);
    }

    public function test_rerun_same_month_replace_semantics(): void
    {
        $this->withSession([
            'user_id' => 12,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('select')->times(10)->andReturn([], [], [], [], [], [], [], [], [], []);
        DB::shouldReceive('beginTransaction')->twice();
        DB::shouldReceive('delete')->times(4)->andReturn(0); // 2 per finalize run
        DB::shouldReceive('insert')->times(2)->andReturnTrue(); // util_billing_run insert per run
        DB::shouldReceive('selectOne')->times(2)->andReturn((object) ['id' => 900], (object) ['id' => 901]);
        DB::shouldReceive('statement')->twice()->andReturnTrue();
        DB::shouldReceive('commit')->twice();

        $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026'])->assertOk();
        $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026'])->assertOk();
    }

    public function test_duplicate_hr_conflict_path(): void
    {
        $this->withSession([
            'user_id' => 14,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('select')->once()->andReturn([(object) ['company_id' => 'E-1', 'c' => 2]]);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(2)->andReturn(0);
        DB::shouldReceive('insert')->times(2)->andReturnTrue(); // run + audit
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 321]);
        DB::shouldReceive('commit')->once();

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertStatus(409)->assertJsonPath('status', 'failed')->assertJsonPath('run_id', 321);
    }

    public function test_response_keys_present(): void
    {
        $this->withSession([
            'user_id' => 13,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(2)->andReturn(0);
        DB::shouldReceive('insert')->once()->andReturnTrue();
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 1001]);
        DB::shouldReceive('statement')->once()->andReturnTrue();
        DB::shouldReceive('commit')->once();

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonStructure(['status', 'run_id', 'rows']);
    }
}
