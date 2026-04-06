<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1BundleFilterCorrectnessTest extends TestCase
{
    public function test_bundle_accepts_run_id_filter(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'VIEWER','force_change_password'=>0]);
        $res = $this->getJson('/api/electric-v1/outputs?cycle_start=2026-03-01&cycle_end=2026-03-31&run_id=RUN-X');
        $res->assertStatus(200)->assertJsonStructure(['status','data'=>['final_outputs','drilldown_outputs','exceptions','run_history']]);
    }
}
