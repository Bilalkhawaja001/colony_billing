<?php

namespace Tests\Feature\ElectricV1;

use Tests\Support\ElectricV1FixtureRunner;
use Tests\Support\ElectricV1SnapshotNormalizer;
use Tests\TestCase;

class ElectricV1ParityFixtureTest extends TestCase
{
    private const CASES = [
        'case_01_happy_path_room_split',
        'case_02_reverse_read_reject',
        'case_03_faulty_read_estimate_3_valid_cycles',
        'case_04_missing_read_estimate_insufficient_history',
        'case_05_house_not_single_responsible',
        'case_06_room_consumption_zero_attendance_skip',
        'case_07_adjustment_negative_floor_to_zero',
        'case_08_rounding_boundaries_050_051',
        'case_09_duplicate_allowance_key',
        'case_10_duplicate_reading_key_same_cycle',
        'case_11_multi_unit_multi_room_overlap',
        'case_12_rerun_same_cycle_replace_only',
    ];

    public function test_all_canonical_fixture_cases_match_expected_bundle(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);

        foreach (self::CASES as $case) {
            $r = ElectricV1FixtureRunner::runCase($this, $case);

            $actual = ElectricV1SnapshotNormalizer::normalize([
                'final_outputs' => $r['bundle']['final_outputs'] ?? [],
                'drilldown_outputs' => $r['bundle']['drilldown_outputs'] ?? [],
                'exceptions' => $r['bundle']['exceptions'] ?? [],
                'run_history' => [],
            ]);
            $expected = ElectricV1SnapshotNormalizer::normalize([
                'final_outputs' => $r['expected']['final_outputs'] ?? [],
                'drilldown_outputs' => $r['expected']['drilldown_outputs'] ?? [],
                'exceptions' => $r['expected']['exceptions'] ?? [],
                'run_history' => [],
            ]);

            if ($actual !== $expected) {
                $diff = ElectricV1FixtureRunner::writeDiff($r['base'], $expected, $actual);
                $this->fail("Canonical parity mismatch in {$case}. Diff: {$diff}");
            }

            $summary = $r['run']['data'] ?? [];
            $expSummary = $r['expected']['run_summary'] ?? [];
            foreach (['status','processed_count','skipped_count','exception_count','final_output_rows','drilldown_rows'] as $k) {
                if (array_key_exists($k, $expSummary)) {
                    $this->assertSame($expSummary[$k], $summary[$k] ?? null, "Run summary mismatch {$case} field {$k}");
                }
            }
        }

        $this->assertTrue(true);
    }
}
