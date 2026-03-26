<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ElectricV1ApiTest extends TestCase
{
    public function test_run_requires_cycle_and_rate(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);
        $this->postJson('/api/electric-v1/run', [])->assertStatus(422);
    }

    public function test_outputs_bundle_shape(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'VIEWER','force_change_password'=>0]);

        DB::shouldReceive('select')->times(4)->andReturn([], [], [], []);

        $this->getJson('/api/electric-v1/outputs?cycle_start=2026-03-01&cycle_end=2026-03-31')
            ->assertOk()->assertJsonStructure(['status','data'=>['cycle_start_date','cycle_end_date','final_outputs','drilldown_outputs','exceptions','run_history']]);
    }

    public function test_exceptions_and_runs_endpoints_work(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'VIEWER','force_change_password'=>0]);
        DB::shouldReceive('select')->times(2)->andReturn([], []);

        $this->getJson('/api/electric-v1/exceptions?cycle_start=2026-03-01&cycle_end=2026-03-31')->assertOk();
        $this->getJson('/api/electric-v1/runs?cycle_start=2026-03-01&cycle_end=2026-03-31')->assertOk();
    }
}
