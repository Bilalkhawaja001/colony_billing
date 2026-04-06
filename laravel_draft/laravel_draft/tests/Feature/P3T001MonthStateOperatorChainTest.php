<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P3T001MonthStateOperatorChainTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9301,
            'actor_user_id' => 9301,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function asViewer(): void
    {
        $this->withSession([
            'user_id' => 9302,
            'actor_user_id' => 9302,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public function test_p3_t001_month_state_governed_operator_chain_end_to_end(): void
    {
        $this->asAdmin();

        $month = '09-2026';

        // 1) open/transition + invalid transition guard
        $this->postJson('/month/open', ['month_cycle' => $month])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('state', 'OPEN');

        $this->assertSame('OPEN', DB::table('util_month_cycle')->where('month_cycle', $month)->value('state'));

        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'INGEST'])
            ->assertOk()
            ->assertJsonPath('state', 'INGEST');

        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'VALIDATION'])
            ->assertOk()
            ->assertJsonPath('state', 'VALIDATION');

        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'INVALID_STATE'])
            ->assertStatus(422);

        // 2) rates config/approve persistence
        $this->postJson('/monthly-rates/config/upsert', [
            'month_cycle' => $month,
            'elec_rate' => 31.5,
            'water_general_rate' => 2.25,
            'water_drinking_rate' => 3.50,
            'school_van_rate' => 1200,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->assertNotNull(
            DB::table('util_monthly_rates_config')->where('month_cycle', $month)->first()
        );

        $this->postJson('/rates/approve', ['month_cycle' => $month])
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertSame('APPROVAL', DB::table('util_month_cycle')->where('month_cycle', $month)->value('state'));

        // reload persistence for touched module
        $this->getJson('/monthly-rates/config?month_cycle='.$month)
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('row.month_cycle', $month);

        // 3) import preview/validate/error loop
        $preview = $this->postJson('/imports/meter-register/ingest-preview', [
            'month_cycle' => $month,
            'rows_received' => 12,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $token = (string)$preview->json('token');
        $this->assertNotSame('', $token);

        $this->postJson('/imports/mark-validated', [
            'month_cycle' => $month,
            'token' => $token,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->getJson('/imports/error-report/'.$token)
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('token', $token);

        // 4) billing run/lock/finalized visibility with downstream DB persistence
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E9001', 8, 400, 10, 30]);

        $run = $this->postJson('/billing/run', [
            'month_cycle' => $month,
            'run_key' => 'P3-T001-RUN',
        ])->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('run_key', 'P3-T001-RUN');

        $runId = (int)$run->json('run_id');
        $this->assertGreaterThan(0, $runId);

        $this->assertSame('APPROVED', DB::table('util_billing_run')->where('id', $runId)->value('run_status'));

        $this->assertGreaterThan(
            0,
            DB::table('util_billing_line')->where('billing_run_id', $runId)->count()
        );

        $this->postJson('/billing/lock', ['run_id' => $runId])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('run_status', 'LOCKED');

        $this->assertSame('LOCKED', DB::table('util_billing_run')->where('id', $runId)->value('run_status'));

        // finalized visibility + reload checks
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'LOCKED'])
            ->assertOk()
            ->assertJsonPath('state', 'LOCKED');

        $this->get('/ui/finalized-months?month_cycle='.$month)
            ->assertOk()
            ->assertSee('Finalized Months Workspace')
            ->assertSee($month);

        $this->getJson('/reports/monthly-summary?month_cycle='.$month)
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        // 5) locked-month write block
        $this->postJson('/monthly-rates/config/upsert', [
            'month_cycle' => $month,
            'elec_rate' => 35,
            'water_general_rate' => 2.25,
            'water_drinking_rate' => 3.50,
            'school_van_rate' => 1200,
        ])->assertStatus(409)->assertJsonPath('guard', 'month.guard.domain');

        // 6) role denial
        $this->asViewer();
        $this->postJson('/billing/run', [
            'month_cycle' => $month,
            'run_key' => 'P3-T001-ROLE-DENY',
        ])->assertStatus(403);
    }
}
