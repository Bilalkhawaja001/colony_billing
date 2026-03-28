<?php

namespace Tests\Feature\ElectricV1;

use Tests\Support\ElectricV1SnapshotNormalizer;
use Tests\TestCase;

class ElectricV1DeterminismHashTest extends TestCase
{
    public function test_same_inputs_produce_same_hash(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);

        $payload = ['cycle_start'=>'2026-05-01','cycle_end'=>'2026-05-31','flat_rate'=>2.5];
        $r1 = $this->postJson('/api/electric-v1/run', $payload)->assertOk()->json('data');
        $b1 = $this->getJson('/api/electric-v1/outputs?cycle_start=2026-05-01&cycle_end=2026-05-31&run_id='.urlencode($r1['run_id']))->json('data');

        $r2 = $this->postJson('/api/electric-v1/run', $payload)->assertOk()->json('data');
        $b2 = $this->getJson('/api/electric-v1/outputs?cycle_start=2026-05-01&cycle_end=2026-05-31&run_id='.urlencode($r2['run_id']))->json('data');

        $comp1 = ['final_outputs'=>$b1['final_outputs'] ?? [], 'drilldown_outputs'=>$b1['drilldown_outputs'] ?? [], 'exceptions'=>$b1['exceptions'] ?? []];
        $comp2 = ['final_outputs'=>$b2['final_outputs'] ?? [], 'drilldown_outputs'=>$b2['drilldown_outputs'] ?? [], 'exceptions'=>$b2['exceptions'] ?? []];

        $this->assertEquals(ElectricV1SnapshotNormalizer::hash($comp1), ElectricV1SnapshotNormalizer::hash($comp2));
    }
}
