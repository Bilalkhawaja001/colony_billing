<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResultsLogsParityTest extends TestCase
{
    public function test_results_endpoints_and_ui_routes(): void
    {
        $this->withSession(['user_id' => 10, 'role' => 'BILLING_ADMIN', 'force_change_password' => 0]);

        DB::table('billing_rows')->insert([
            ['month_cycle' => '03-2026', 'company_id' => 'E1', 'unit_id' => 'U1', 'water_amt' => 10, 'power_amt' => 20, 'drink_amt' => 3, 'total_amt' => 33, 'created_at' => now(), 'updated_at' => now()],
            ['month_cycle' => '03-2026', 'company_id' => 'E2', 'unit_id' => 'U1', 'water_amt' => 5, 'power_amt' => 10, 'drink_amt' => 1, 'total_amt' => 16, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->getJson('/api/results/employee-wise?month_cycle=03-2026')
            ->assertOk()->assertJsonPath('rows.0.company_id', 'E1');

        $this->getJson('/api/results/unit-wise?month_cycle=03-2026')
            ->assertOk()->assertJsonPath('rows.0.total_amt', 49);

        $this->get('/ui/family-details')->assertOk();
        $this->get('/ui/results/employee-wise')->assertOk();
        $this->get('/ui/results/unit-wise')->assertOk();

        $this->getJson('/api/logs?month_cycle=03-2026')->assertStatus(403);
    }

    public function test_logs_endpoint_and_ui_allowed_for_super_admin(): void
    {
        $this->withSession(['user_id' => 1, 'role' => 'SUPER_ADMIN', 'force_change_password' => 0]);
        DB::table('logs')->insert([
            'month_cycle' => '03-2026', 'severity' => 'INFO', 'code' => 'RUN_OK', 'message' => 'Done', 'ref_json' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/api/logs?month_cycle=03-2026')->assertOk()->assertJsonPath('rows.0.code', 'RUN_OK');
        $this->get('/ui/logs')->assertOk();
    }
}
