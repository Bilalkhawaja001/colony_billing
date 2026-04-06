<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P3T004FamilyResultsReportingLinkTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9601,
            'actor_user_id' => 9601,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function asViewer(): void
    {
        $this->withSession([
            'user_id' => 9602,
            'actor_user_id' => 9602,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public function test_p3_t004_family_results_logs_cross_link_into_reporting_surfaces(): void
    {
        $this->asAdmin();
        $month = '12-2026';

        // Month + run context
        $this->postJson('/month/open', ['month_cycle' => $month])->assertOk();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'APPROVAL'])->assertOk();

        // Seed registry/master so family context/report joins remain consistent
        DB::statement("INSERT INTO employees_master(company_id,name,department,designation,active,created_at,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", ['E1201', 'Emp 1201', 'Ops', 'Tech', 'YES']);

        // Family details write + reload persistence
        $this->postJson('/family/details/upsert', [
            'month_cycle' => $month,
            'company_id' => 'E1201',
            'family_member_name' => 'Child A',
            'relation' => 'Son',
            'age' => 8,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->getJson('/family/details?month_cycle='.$month.'&company_id=E1201')
            ->assertOk()->assertJsonPath('status', 'ok');

        // Validation failure path
        $this->postJson('/family/details/upsert', [
            'month_cycle' => $month,
            'company_id' => '',
            'family_member_name' => 'Broken',
            'relation' => 'Son',
            'age' => 7,
        ])->assertStatus(400);

        // Downstream report source rows
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E1201', 10, 500, 50, 125]);
        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E1201', 'Child A', 'School Z', '2', 'BOTH_WAY', 700, 700, 1]);

        $run = $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'P3-T004-RUN'])
            ->assertOk()->assertJsonPath('status', 'ok');
        $runId = (int)$run->json('run_id');
        $this->assertGreaterThan(0, $runId);

        // Seed results/logs source tables used by T-014 parity endpoints
        DB::statement("INSERT INTO billing_rows(month_cycle,company_id,unit_id,water_amt,power_amt,drink_amt,total_amt) VALUES(?,?,?,?,?,?,?)", [$month, 'E1201', 'U120', 125, 500, 0, 625]);
        DB::statement("INSERT INTO logs(month_cycle,severity,code,message,ref_json,created_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)", [$month, 'INFO', 'P3_T004', 'seeded log', '{}']);

        // T-014 result surfaces
        $empWise = $this->getJson('/api/results/employee-wise?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');
        $this->assertGreaterThan(0, count((array)$empWise->json('rows')));

        $unitWise = $this->getJson('/api/results/unit-wise?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');
        $this->assertIsArray($unitWise->json('rows'));

        // logs surface (SUPER_ADMIN allowed)
        $this->getJson('/api/logs?month_cycle='.$month)->assertOk();

        // Cross-link into report surfaces (T-016)
        $empBill = $this->getJson('/reports/employee-bill-summary?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        $rows = collect($empBill->json('rows'));
        $row = $rows->firstWhere('employee_id', 'E1201');
        $this->assertNotNull($row);
        $this->assertSame(1, (int)($row['has_family'] ?? 0));

        $this->getJson('/reports/monthly-summary?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->getJson('/reports/reconciliation?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        // Reload persistence checks
        $this->getJson('/api/results/employee-wise?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');
        $this->getJson('/reports/employee-bill-summary?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');

        // Role behavior: viewer blocked on family write, read still allowed on reports
        $this->asViewer();
        $this->postJson('/family/details/upsert', [
            'month_cycle' => $month,
            'company_id' => 'E1201',
            'family_member_name' => 'Child B',
            'relation' => 'Daughter',
            'age' => 6,
        ])->assertStatus(403);

        $this->getJson('/reports/employee-bill-summary?month_cycle='.$month)
            ->assertOk()->assertJsonPath('status', 'ok');
    }
}
