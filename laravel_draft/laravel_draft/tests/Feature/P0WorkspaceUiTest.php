<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class P0WorkspaceUiTest extends TestCase
{
    private function auth(): void
    {
        $this->withSession([
            'user_id' => 8001,
            'actor_user_id' => 8001,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    public function test_month_cycle_page_is_real_not_shell_and_actions_work(): void
    {
        $this->auth();

        $this->get('/ui/month-cycle')
            ->assertOk()
            ->assertSee('Month Cycle Governance')
            ->assertDontSee('Parity draft page is active');

        $this->postJson('/month/open', ['month_cycle' => '05-2026'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->postJson('/month/transition', ['month_cycle' => '05-2026', 'to_state' => 'APPROVAL'])
            ->assertOk()->assertJsonPath('status', 'ok');
    }

    public function test_rates_page_is_real_not_shell_and_core_actions_work(): void
    {
        $this->auth();

        $this->get('/ui/rates')
            ->assertOk()
            ->assertSee('Rates Workspace')
            ->assertDontSee('Parity draft page is active');

        $this->postJson('/rates/upsert', [
            'month_cycle' => '05-2026',
            'elec_rate' => 50,
            'water_general_rate' => 0.2,
            'water_drinking_rate' => 0.5,
            'school_van_rate' => 4500,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->postJson('/rates/approve', ['month_cycle' => '05-2026'])
            ->assertOk()->assertJsonPath('status', 'ok');
    }

    public function test_imports_page_is_real_not_shell_and_core_actions_work(): void
    {
        $this->auth();

        $this->get('/ui/imports')
            ->assertOk()
            ->assertSee('Imports Workspace')
            ->assertDontSee('Parity draft page is active');

        $preview = $this->postJson('/imports/meter-register/ingest-preview', [
            'month_cycle' => '05-2026',
            'rows_received' => 12,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $token = (string)($preview->json('token') ?? 'tok-demo');

        $this->postJson('/imports/mark-validated', ['token' => $token])
            ->assertOk()->assertJsonPath('status', 'ok');
    }

    public function test_billing_page_is_real_not_shell_and_run_path_works(): void
    {
        $this->auth();

        DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('05-2026','OPEN')");
        DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES('05-2026','E101',10,500,100,20,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
        DB::statement("INSERT INTO util_drinking_formula_result(month_cycle,employee_id,billed_liters,rate,amount,created_at,updated_at) VALUES('05-2026','E101',20,0.5,10,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
        DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES('05-2026','E101','Kid','School','1','BOTH_WAY',100,100,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");

        $this->get('/ui/billing')
            ->assertOk()
            ->assertSee('Billing Workspace')
            ->assertDontSee('Parity draft page is active');

        $this->postJson('/billing/run', ['month_cycle' => '05-2026', 'run_key' => 'P0-UI'])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('run_status', 'APPROVED');
    }
}
