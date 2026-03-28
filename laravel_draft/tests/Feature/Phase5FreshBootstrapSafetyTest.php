<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase5FreshBootstrapSafetyTest extends TestCase
{
    public function test_required_non_ev1_tables_exist_after_fresh_bootstrap(): void
    {
        $need = [
            'util_month_cycle','util_rate_monthly','util_billing_run','util_billing_line',
            'util_formula_result','util_drinking_formula_result','util_school_van_monthly_charge',
            'billing_run','billing_rows','logs','finalized_months','hr_input','map_room','readings','ro_drinking',
        ];

        foreach ($need as $t) {
            $row = DB::selectOne("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$t]);
            $this->assertNotNull($row, "Missing table after bootstrap: {$t}");
        }
    }

    public function test_fresh_db_non_ev1_run_report_lock_and_finalize_paths_boot(): void
    {
        $this->withSession([
            'user_id' => 7001,
            'actor_user_id' => 7001,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        // util run/report/lock path dataset (LIMITED GO path)
        DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('03-2026','OPEN')");
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount) VALUES('03-2026','E100',10,500,100,20)");
        DB::statement("INSERT INTO util_drinking_formula_result(month_cycle,employee_id,billed_liters,rate,amount) VALUES('03-2026','E100',20,0.5,10)");
        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES('03-2026','E100','Kid','School','1','BOTH_WAY',100,100,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");

        $run = $this->postJson('/billing/run', ['month_cycle' => '03-2026', 'run_key' => 'PHASE5-RUN']);
        $run->assertOk()->assertJsonPath('status', 'ok')->assertJsonPath('run_status', 'APPROVED');
        $runId = (int) $run->json('run_id');
        $this->assertGreaterThan(0, $runId);

        $this->getJson('/reports/monthly-summary?month_cycle=03-2026')
            ->assertOk()
            ->assertJsonPath('month_cycle', '03-2026');

        DB::statement("UPDATE util_month_cycle SET state='APPROVAL' WHERE month_cycle='03-2026'");
        $this->postJson('/billing/lock', ['run_id' => $runId])->assertOk()->assertJsonPath('run_status', 'LOCKED');

        // locked month must block run writes
        DB::statement("UPDATE util_month_cycle SET state='LOCKED' WHERE month_cycle='03-2026'");
        $this->postJson('/billing/run', ['month_cycle' => '03-2026', 'run_key' => 'PHASE5-LOCKED'])
            ->assertStatus(409);

        // finalize path dataset (legacy finalize chain retained in LIMITED GO)
        DB::statement("INSERT INTO hr_input(month_cycle,company_id,active_days) VALUES('04-2026','E100',30)");
        DB::statement("INSERT INTO map_room(month_cycle,unit_id,company_id) VALUES('04-2026','U1','E100')");
        DB::statement("INSERT INTO readings(month_cycle,meter_id,unit_id,meter_type,usage,amount) VALUES('04-2026','M1','U1','ELEC',10,500)");
        DB::statement("INSERT INTO ro_drinking(month_cycle,unit_id,liters,amount) VALUES('04-2026','U1',20,10)");

        $this->postJson('/api/billing/finalize', ['month_cycle' => '04-2026'])
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
