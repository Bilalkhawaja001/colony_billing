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
            'month_guard_locked' => true, // finalize is configured exception path
        ]);

        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(3)->andReturn(0);
        DB::shouldReceive('insert')->times(1)->andReturnTrue();
        DB::shouldReceive('statement')->once()->andReturnTrue();
        DB::shouldReceive('commit')->once();

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertOk()
            ->assertJsonStructure(['status', 'run_id', 'rows'])
            ->assertJsonPath('status', 'ok');
    }

    public function test_invalid_month_input(): void
    {
        $this->withSession([
            'user_id' => 10,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
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
            'month_guard_locked' => false,
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
            'month_guard_locked' => true,
        ]);

        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(3)->andReturn(0);
        DB::shouldReceive('insert')->times(1)->andReturnTrue();
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
            'month_guard_locked' => false,
        ]);

        DB::shouldReceive('select')->times(10)->andReturn([], [], [], [], [], [], [], [], [], []);
        DB::shouldReceive('beginTransaction')->twice();
        DB::shouldReceive('delete')->times(6)->andReturn(0); // 3 per finalize run
        DB::shouldReceive('insert')->times(2)->andReturnTrue(); // billing_run final insert per run
        DB::shouldReceive('statement')->twice()->andReturnTrue(); // finalized_months upsert
        DB::shouldReceive('commit')->twice();

        $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026'])->assertOk();
        $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026'])->assertOk();
    }

    public function test_response_keys_present(): void
    {
        $this->withSession([
            'user_id' => 13,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
            'month_guard_locked' => false,
        ]);

        DB::shouldReceive('select')->times(5)->andReturn([], [], [], [], []);
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('delete')->times(3)->andReturn(0);
        DB::shouldReceive('insert')->times(1)->andReturnTrue();
        DB::shouldReceive('statement')->once()->andReturnTrue();
        DB::shouldReceive('commit')->once();

        $res = $this->postJson('/api/billing/finalize', ['month_cycle' => '03-2026']);
        $res->assertOk()->assertJsonStructure(['status', 'run_id', 'rows']);
    }
}
