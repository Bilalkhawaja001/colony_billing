<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase6ElecPersistenceParityTest extends TestCase
{
    public function test_final_persisted_lines_match_flask_authoritative_set_without_elec(): void
    {
        $this->withSession([
            'user_id' => 7301,
            'actor_user_id' => 7301,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::statement("DELETE FROM util_billing_line");
        DB::statement("DELETE FROM util_billing_run");
        DB::statement("DELETE FROM util_formula_result");
        DB::statement("DELETE FROM util_drinking_formula_result");
        DB::statement("DELETE FROM util_school_van_monthly_charge");
        DB::statement("DELETE FROM util_month_cycle");

        DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('03-2026','OPEN')");
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES('03-2026','E100',10,500,100,20,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
        DB::statement("INSERT INTO util_drinking_formula_result(month_cycle,employee_id,billed_liters,rate,amount,created_at,updated_at) VALUES('03-2026','E100',20,0.5,10,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES('03-2026','E100','Kid','School','1','BOTH_WAY',100,100,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");

        $res = $this->postJson('/billing/run', ['month_cycle' => '03-2026', 'run_key' => 'PARITY-ELEC']);
        $res->assertOk();

        $runId = (int) $res->json('run_id');
        $this->assertGreaterThan(0, $runId);

        $rows = DB::select('SELECT utility_type, ROUND(amount,2) AS amount FROM util_billing_line WHERE billing_run_id=? ORDER BY utility_type', [$runId]);
        $types = array_map(fn ($r) => (string)$r->utility_type, $rows);

        $this->assertCount(3, $rows, 'Persisted line count must match Flask authoritative behavior (3).');
        $this->assertSame(['SCHOOL_VAN', 'WATER_DRINKING', 'WATER_GENERAL'], $types);
        $this->assertFalse(in_array('ELEC', $types, true), 'ELEC must not remain in final persisted summary set.');
    }
}
