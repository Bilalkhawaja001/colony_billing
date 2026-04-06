<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P3T002InputsComputeElecSummaryTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9401,
            'actor_user_id' => 9401,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function asViewer(): void
    {
        $this->withSession([
            'user_id' => 9402,
            'actor_user_id' => 9402,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public function test_p3_t002_inputs_to_compute_run_to_elec_summary_chain(): void
    {
        $this->asAdmin();
        $month = '10-2026';

        // month open for write operations
        $this->postJson('/month/open', ['month_cycle' => $month])->assertOk();

        // T-010 input pages present (operator touchpoints)
        $this->get('/ui/inputs/mapping')->assertOk()->assertSee('Inputs Mapping Workspace');
        $this->get('/ui/inputs/hr')->assertOk()->assertSee('Inputs HR Workspace');
        $this->get('/ui/inputs/readings')->assertOk()->assertSee('Inputs Readings Workspace');
        $this->get('/ui/inputs/ro')->assertOk()->assertSee('Inputs RO Workspace');

        // Validation error surface (input workflow): mark-validated requires token
        $this->postJson('/imports/mark-validated', ['month_cycle' => $month])->assertStatus(422);

        // Valid input dataset for finalize propagation
        DB::statement("INSERT INTO hr_input(month_cycle,company_id,active_days) VALUES(?,?,?)", [$month, 'E1001', 30]);
        DB::statement("INSERT INTO map_room(month_cycle,unit_id,company_id) VALUES(?,?,?)", [$month, 'U100', 'E1001']);
        DB::statement("INSERT INTO readings(month_cycle,meter_id,unit_id,meter_type,usage,amount) VALUES(?,?,?,?,?,?)", [$month, 'M100', 'U100', 'ELEC', 15, 750]);
        DB::statement("INSERT INTO ro_drinking(month_cycle,unit_id,liters,amount) VALUES(?,?,?,?)", [$month, 'U100', 50, 20]);

        $this->postJson('/api/billing/finalize', ['month_cycle' => $month])
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertGreaterThan(0, DB::table('util_billing_line')->where('month_cycle', $month)->count());
        $this->assertNotNull(DB::table('finalized_months')->where('month_cycle', $month)->first());

        // Seed run-source formula rows (authoritative T-001 run source contract)
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E1001', 15, 750, 10, 30]);

        // Move month to APPROVAL, then run billing (T-001 run)
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'APPROVAL'])
            ->assertOk()->assertJsonPath('state', 'APPROVAL');

        $run = $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'P3-T002-RUN'])
            ->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('run_key', 'P3-T002-RUN');
        $runId = (int)$run->json('run_id');
        $this->assertGreaterThan(0, $runId);

        $this->assertGreaterThan(0, DB::table('util_billing_line')->where('billing_run_id', $runId)->count());

        // Seed elec summary result tables for T-013 compute/report truth path
        DB::statement("INSERT INTO util_elec_unit_monthly_result(month_cycle,unit_id,category,usage_units,rooms_count,unit_free_units,net_units,elec_rate,unit_amount,total_attendance,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'U100', 'FAMILY', 15, 1, 0, 15, 50, 750, 30]);
        DB::statement("INSERT INTO employees_master(company_id,name,active) VALUES(?,?,?)", ['E1001', 'Emp 1001', 'YES']);
        DB::statement("INSERT INTO util_elec_employee_share_monthly(month_cycle,unit_id,employee_id,attendance,share_units,share_amount,allocation_method,explain_usage_share_units,explain_free_share_units,explain_billable_units,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'U100', 'E1001', 30, 15, 750, 'attendance', 15, 0, 15]);

        // Compute + report reload truth
        $this->postJson('/billing/elec/compute', ['month_cycle' => $month, 'unit_id' => 'U100'])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonCount(1, 'unit_rows')
            ->assertJsonCount(1, 'share_rows');

        $this->getJson('/reports/elec-summary?month_cycle='.$month.'&unit_id=U100')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('summary.unit_count', 1)
            ->assertJsonPath('summary.share_count', 1)
            ->assertJsonPath('share_rows.0.employee_id', 'E1001');

        // Reload persistence across touched modules
        $this->getJson('/reports/elec-summary?month_cycle='.$month.'&unit_id=U100')
            ->assertOk()->assertJsonPath('summary.share_count', 1);
        $this->getJson('/reports/monthly-summary?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        // Role behavior: viewer cannot run/compute write endpoints
        $this->asViewer();
        $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'VIEWER-DENY'])->assertStatus(403);
        $this->postJson('/billing/elec/compute', ['month_cycle' => $month])->assertStatus(403);

        // Locked-month behavior relevant to run
        $this->asAdmin();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'LOCKED'])
            ->assertOk()->assertJsonPath('state', 'LOCKED');
        $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'LOCKED-DENY'])
            ->assertStatus(409);
    }
}
