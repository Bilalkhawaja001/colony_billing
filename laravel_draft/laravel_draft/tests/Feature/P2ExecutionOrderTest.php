<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P2ExecutionOrderTest extends TestCase
{
    private function authAdmin(): void
    {
        $this->withSession([
            'user_id' => 8201,
            'actor_user_id' => 8201,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function authViewer(): void
    {
        $this->withSession([
            'user_id' => 8202,
            'actor_user_id' => 8202,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    // T-011
    public function test_t011_water_module_operational_flow_with_validation_and_reload(): void
    {
        $this->authAdmin();

        $this->get('/ui/water-meters')->assertOk()->assertSee('Water Module Workspace')->assertDontSee('Parity draft page is active');

        // validation error surfaced
        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => '06-2026',
            'rows' => [['water_zone' => 'FAMILY_METER', 'raw_liters' => -1, 'common_use_liters' => 0]],
        ])->assertStatus(400)->assertJsonPath('status', 'error');

        // create/update equivalent upsert
        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => '06-2026',
            'rows' => [['water_zone' => 'FAMILY_METER', 'raw_liters' => 120, 'common_use_liters' => 20]],
        ])->assertOk()->assertJsonPath('status', 'ok');

        // reload/list
        $this->getJson('/api/water/zone-adjustments?month_cycle=06-2026')
            ->assertOk()->assertJsonPath('status', 'ok');

        // role behavior
        $this->authViewer();
        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => '06-2026',
            'rows' => [['water_zone' => 'FAMILY_METER', 'raw_liters' => 200, 'common_use_liters' => 10]],
        ])->assertStatus(403);
    }

    // T-012
    public function test_t012_van_module_load_and_report_side_effect(): void
    {
        $this->authAdmin();

        $this->get('/ui/van')->assertOk()->assertSee('Van Module Workspace')->assertDontSee('Parity draft page is active');

        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES('06-2026','E200','Kid','School','1','BOTH_WAY',100,100,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");

        $this->getJson('/reports/van?month_cycle=06-2026')->assertOk();
    }

    // T-013
    public function test_t013_elec_summary_compute_and_reload_flow(): void
    {
        $this->authAdmin();

        $this->get('/ui/elec-summary')->assertOk()->assertSee('Electric Summary Workspace')->assertDontSee('Parity draft page is active');

        DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('06-2026','OPEN')");
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES('06-2026','E201',10,500,0,0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");

        $this->postJson('/billing/elec/compute', ['month_cycle' => '06-2026'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->getJson('/reports/elec-summary?month_cycle=06-2026')->assertOk()->assertJsonPath('status', 'ok');
    }

    // T-014
    public function test_t014_family_results_logs_depth_with_upsert_and_reload(): void
    {
        $this->authAdmin();

        $this->get('/ui/family-details')->assertOk()->assertSee('Family Details Workspace');
        $this->get('/ui/results/employee-wise')->assertOk()->assertSee('Results Employee-Wise');
        $this->get('/ui/results/unit-wise')->assertOk()->assertSee('Results Unit-Wise');
        $this->get('/ui/logs')->assertOk()->assertSee('Logs Workspace');

        // validation-ish failure path
        $this->postJson('/family/details/upsert', [
            'month_cycle' => '06-2026',
            'company_id' => '',
            'family_member_name' => 'A',
            'relation' => 'Son',
            'age' => 8,
        ])->assertStatus(400);

        // create/update equivalent
        $this->postJson('/family/details/upsert', [
            'month_cycle' => '06-2026',
            'company_id' => 'E202',
            'family_member_name' => 'A',
            'relation' => 'Son',
            'age' => 8,
        ])->assertOk()->assertJsonPath('status', 'ok');

        // reload
        $this->getJson('/family/details?month_cycle=06-2026&company_id=E202')->assertOk();
        $this->getJson('/api/results/employee-wise?month_cycle=06-2026')->assertOk();
        $logsResp = $this->getJson('/api/logs?month_cycle=06-2026');
        $this->assertContains($logsResp->status(), [200, 403]);
    }

    // T-015
    public function test_t015_finalized_months_finalize_and_reload(): void
    {
        $this->authAdmin();

        $this->get('/ui/finalized-months?month_cycle=07-2026')->assertOk()->assertSee('Finalized Months Workspace')->assertDontSee('Parity draft page is active');

        DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('07-2026','OPEN')");
        DB::statement("INSERT INTO hr_input(month_cycle,company_id,active_days) VALUES('07-2026','E300',30)");
        DB::statement("INSERT INTO map_room(month_cycle,unit_id,company_id) VALUES('07-2026','U7','E300')");
        DB::statement("INSERT INTO readings(month_cycle,meter_id,unit_id,meter_type,usage,amount) VALUES('07-2026','M7','U7','ELEC',10,500)");
        DB::statement("INSERT INTO ro_drinking(month_cycle,unit_id,liters,amount) VALUES('07-2026','U7',20,10)");

        $this->postJson('/api/billing/finalize', ['month_cycle' => '07-2026'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->get('/ui/finalized-months?month_cycle=07-2026')->assertOk()->assertSee('07-2026');
    }

    // T-016
    public function test_t016_dashboard_reports_reconciliation_depth_with_exports_and_role_behavior(): void
    {
        $this->authAdmin();

        DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('08-2026','OPEN')");
        DB::statement("INSERT INTO util_billing_run(month_cycle,run_key,run_status,started_by_user_id,created_at,updated_at) VALUES('08-2026','T16','APPROVED','1',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
        $runId = (int)(DB::selectOne("SELECT id FROM util_billing_run WHERE month_cycle='08-2026' AND run_key='T16'")->id ?? 0);
        DB::statement("INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$runId,'08-2026','E400','WATER_GENERAL',1,20,20,'seed']);

        $this->get('/ui/dashboard?month_cycle=08-2026')->assertOk()->assertSee('Dashboard');
        $this->get('/ui/reports?month_cycle=08-2026')->assertOk()->assertSee('Reports');
        $this->get('/ui/reconciliation?month_cycle=08-2026')->assertOk()->assertSee('Reconciliation');

        $this->get('/export/excel/monthly-summary?month_cycle=08-2026')->assertStatus(200);
        $this->get('/export/excel/reconciliation?month_cycle=08-2026')->assertStatus(200);

        // role guard on write action
        $this->authViewer();
        $this->postJson('/billing/run', ['month_cycle' => '08-2026', 'run_key' => 'DENY'])
            ->assertStatus(403);
    }
}
