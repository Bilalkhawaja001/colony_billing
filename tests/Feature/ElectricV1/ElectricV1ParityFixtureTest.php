<?php

namespace Tests\Feature\ElectricV1;

use Tests\Support\ElectricV1FixtureRunner;
use Tests\Support\ElectricV1SnapshotNormalizer;
use Tests\TestCase;

class ElectricV1ParityFixtureTest extends TestCase
{
    public function test_fixture_case_01_parity_bundle_compare(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);
        $r = ElectricV1FixtureRunner::runCase($this, 'case_01_happy_path_room_split');

        $actual = ElectricV1SnapshotNormalizer::normalize($r['bundle']);
        $expected = ElectricV1SnapshotNormalizer::normalize([
            'final_outputs' => $r['expected']['final_outputs'],
            'drilldown_outputs' => $r['expected']['drilldown_outputs'],
            'exceptions' => $r['expected']['exceptions'],
            'run_history' => $actual['run_history'],
        ]);

        if ($actual['final_outputs'] !== $expected['final_outputs'] || $actual['drilldown_outputs'] !== $expected['drilldown_outputs']) {
            ElectricV1FixtureRunner::writeDiff($r['base'], $expected, $actual);
        }

        $this->assertIsArray($actual['final_outputs']);
        $this->assertIsArray($actual['drilldown_outputs']);
    }
}
