<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P3T006IntegratedRerunIdempotencyTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9801,
            'actor_user_id' => 9801,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function snapshotForMonth(string $month): array
    {
        $runId = (int) (DB::table('util_billing_run')
            ->where('month_cycle', $month)
            ->whereIn('run_status', ['APPROVED', 'LOCKED'])
            ->orderByDesc('id')
            ->value('id') ?? 0);

        $lines = DB::table('util_billing_line')
            ->where('billing_run_id', $runId)
            ->orderBy('employee_id')
            ->orderBy('utility_type')
            ->get(['employee_id', 'utility_type', 'qty', 'rate', 'amount'])
            ->map(fn ($r) => [
                'employee_id' => (string) $r->employee_id,
                'utility_type' => (string) $r->utility_type,
                'qty' => (float) $r->qty,
                'rate' => (float) $r->rate,
                'amount' => (float) $r->amount,
            ])->values()->all();

        $monthly = $this->getJson('/reports/monthly-summary?month_cycle='.$month)->assertOk()->json();
        $recon = $this->getJson('/reports/reconciliation?month_cycle='.$month)->assertOk()->json();
        $emp = $this->getJson('/reports/employee-bill-summary?month_cycle='.$month)->assertOk()->json();

        return [
            'run_id' => $runId,
            'line_count' => count($lines),
            'line_hash' => md5(json_encode($lines)),
            'monthly_hash' => md5(json_encode($monthly)),
            'recon_hash' => md5(json_encode($recon)),
            'emp_hash' => md5(json_encode($emp)),
            'finalized_row_count' => (int) DB::table('finalized_months')->where('month_cycle', $month)->count(),
        ];
    }

    public function test_p3_t006_integrated_rerun_idempotency_and_completeness_gate(): void
    {
        $this->asAdmin();
        $month = '02-2027';

        // Integrated setup (covers prior P3 chain dependencies)
        $this->postJson('/month/open', ['month_cycle' => $month])->assertOk();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'INGEST'])->assertOk();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'VALIDATION'])->assertOk();

        DB::statement("INSERT INTO employees_master(company_id,name,department,designation,active,created_at,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", ['E6201', 'Emp 6201', 'Ops', 'Tech', 'YES']);
        DB::statement("INSERT INTO map_room(month_cycle,unit_id,company_id) VALUES(?,?,?)", [$month, 'U620', 'E6201']);
        DB::statement("INSERT INTO hr_input(month_cycle,company_id,active_days) VALUES(?,?,?)", [$month, 'E6201', 30]);
        DB::statement("INSERT INTO readings(month_cycle,meter_id,unit_id,meter_type,usage,amount) VALUES(?,?,?,?,?,?)", [$month, 'M620', 'U620', 'ELEC', 20, 1000]);
        DB::statement("INSERT INTO ro_drinking(month_cycle,unit_id,liters,amount) VALUES(?,?,?,?)", [$month, 'U620', 40, 20]);

        $this->postJson('/api/billing/finalize', ['month_cycle' => $month])->assertOk()->assertJsonPath('status', 'ok');

        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E6201', 'Kid 620', 'School 620', '3', 'BOTH_WAY', 900, 900, 1]);

        $this->postJson('/family/details/upsert', [
            'month_cycle' => $month,
            'company_id' => 'E6201',
            'family_member_name' => 'Kid 620',
            'relation' => 'Son',
            'age' => 9,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => $month,
            'rows' => [
                ['water_zone' => 'FAMILY_METER', 'raw_liters' => 4000, 'common_use_liters' => 400],
                ['water_zone' => 'BACHELOR_METER', 'raw_liters' => 0, 'common_use_liters' => 0],
                ['water_zone' => 'ADMIN_METER', 'raw_liters' => 0, 'common_use_liters' => 0],
                ['water_zone' => 'TANKER_ZONE', 'raw_liters' => 0, 'common_use_liters' => 0],
            ],
        ])->assertOk()->assertJsonPath('status', 'ok');

        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)", [$month, 'E6201', 20, 1000, 60, 180]);

        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'APPROVAL'])->assertOk();

        // Run #1
        $run1 = $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'P3-T006-RUN'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->assertGreaterThan(0, (int) $run1->json('run_id'));

        $snap1 = $this->snapshotForMonth($month);

        // Run #2 (same input, same run_key) => deterministic/idempotent
        $run2 = $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'P3-T006-RUN'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->assertSame((int) $run1->json('run_id'), (int) $run2->json('run_id'));

        $snap2 = $this->snapshotForMonth($month);

        // Determinism/idempotency gate
        $this->assertSame($snap1['line_count'], $snap2['line_count']);
        $this->assertSame($snap1['line_hash'], $snap2['line_hash']);
        $this->assertSame($snap1['monthly_hash'], $snap2['monthly_hash']);
        $this->assertSame($snap1['recon_hash'], $snap2['recon_hash']);
        $this->assertSame($snap1['emp_hash'], $snap2['emp_hash']);
        $this->assertSame(1, $snap2['finalized_row_count']);

        // Side-effect exports stay active
        $this->get('/export/excel/monthly-summary?month_cycle='.$month)->assertStatus(200);
        $this->get('/export/excel/reconciliation?month_cycle='.$month)->assertStatus(200);
    }
}
