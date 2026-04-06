<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P3T003WaterVanReportingImpactTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9501,
            'actor_user_id' => 9501,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function asViewer(): void
    {
        $this->withSession([
            'user_id' => 9502,
            'actor_user_id' => 9502,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public function test_p3_t003_water_van_downstream_impact_into_reports_and_reconciliation(): void
    {
        $this->asAdmin();
        $month = '11-2026';

        // Month state setup
        $this->postJson('/month/open', ['month_cycle' => $month])->assertOk()->assertJsonPath('state', 'OPEN');

        // T-011 water module operational write + persistence
        $this->get('/ui/water-meters')->assertOk()->assertSee('Water Module Workspace');

        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => $month,
            'rows' => [
                ['water_zone' => 'FAMILY_METER', 'raw_liters' => 5000, 'common_use_liters' => 500],
                ['water_zone' => 'BACHELOR_METER', 'raw_liters' => 2000, 'common_use_liters' => 200],
            ],
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->assertGreaterThan(0, DB::table('util_water_zone_monthly_input')->where('month_cycle', $month)->count());

        $this->getJson('/api/water/zone-adjustments?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->getJson('/api/water/allocation-preview?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        // T-012 van module side-effect data for downstream reports
        $this->get('/ui/van')->assertOk()->assertSee('Van Module Workspace');

        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E1101', 'Kid A', 'School X', '3', 'BOTH_WAY', 1200, 1200, 1]);

        // Source rows for billing run -> report/reconciliation downstream
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E1101', 0, 0, 100, 250]);

        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'APPROVAL'])
            ->assertOk()->assertJsonPath('state', 'APPROVAL');

        $run = $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'P3-T003-RUN'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $runId = (int)$run->json('run_id');
        $this->assertGreaterThan(0, $runId);

        // DB checkpoints for downstream truth
        $this->assertGreaterThan(0, DB::table('util_billing_line')->where('billing_run_id', $runId)->where('utility_type', 'SCHOOL_VAN')->count());
        $this->assertGreaterThan(0, DB::table('util_billing_line')->where('billing_run_id', $runId)->where('utility_type', 'WATER_GENERAL')->count());

        // T-016 downstream surfaces
        $this->get('/ui/dashboard?month_cycle='.$month)->assertOk()->assertSee('Dashboard');
        $this->get('/ui/reports?month_cycle='.$month)->assertOk()->assertSee('Reports');
        $this->get('/ui/reconciliation?month_cycle='.$month)->assertOk()->assertSee('Reconciliation');

        $this->getJson('/reports/van?month_cycle='.$month)
            ->assertOk()->assertJsonCount(1, 'rows');

        $summary = $this->getJson('/reports/monthly-summary?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        $utilities = collect($summary->json('rows'))->pluck('utility_type')->values()->all();
        $this->assertContains('SCHOOL_VAN', $utilities);
        $this->assertContains('WATER_GENERAL', $utilities);

        $this->getJson('/reports/reconciliation?month_cycle='.$month)
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('summary.billed_total', 1450);

        // Export/report side-effects
        $this->get('/export/excel/monthly-summary?month_cycle='.$month)->assertStatus(200);
        $this->get('/export/excel/reconciliation?month_cycle='.$month)->assertStatus(200);

        // Reload persistence checks
        $this->getJson('/reports/monthly-summary?month_cycle='.$month)->assertOk()->assertJsonPath('summary.grand_total_amount', 1450);
        $this->getJson('/reports/reconciliation?month_cycle='.$month)->assertOk()->assertJsonPath('summary.outstanding_total', 1450);

        // Role/access behavior
        $this->asViewer();
        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => $month,
            'rows' => [['water_zone' => 'FAMILY_METER', 'raw_liters' => 10, 'common_use_liters' => 1]],
        ])->assertStatus(403);

        // Locked-month behavior relevant to billing run
        $this->asAdmin();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'LOCKED'])
            ->assertOk()->assertJsonPath('state', 'LOCKED');

        $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'LOCKED-DENY'])
            ->assertStatus(409);
    }
}
