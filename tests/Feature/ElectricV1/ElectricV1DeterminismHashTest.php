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
        $this->postJson('/api/electric-v1/run', $payload)->assertOk();
        $b1 = $this->getJson('/api/electric-v1/outputs?cycle_start=2026-05-01&cycle_end=2026-05-31')->json('data');

        $this->postJson('/api/electric-v1/run', $payload)->assertOk();
        $b2 = $this->getJson('/api/electric-v1/outputs?cycle_start=2026-05-01&cycle_end=2026-05-31')->json('data');

        $this->assertEquals(ElectricV1SnapshotNormalizer::hash($b1), ElectricV1SnapshotNormalizer::hash($b2));
    }
}
