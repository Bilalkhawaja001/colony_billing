<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ElectricV1FixtureRunner
{
    private static function postUpsertIfAny(TestCase $t, string $url, $rows): void
    {
        if (!is_array($rows) || count($rows) === 0) {
            return;
        }
        $t->postJson($url, ['rows' => $rows])->assertStatus(200);
    }

    private static function resetElectricV1State(): void
    {
        foreach ([
            'electric_v1_output_employee_unit_drilldown',
            'electric_v1_output_employee_final',
            'electric_v1_exception_log',
            'electric_v1_run_history',
            'electric_v1_readings',
            'electric_v1_hr_attendance',
            'electric_v1_occupancy',
            'electric_v1_manual_adjustments',
            'electric_v1_adjustments',
            'electric_v1_allowance',
        ] as $tbl) {
            try { DB::table($tbl)->delete(); } catch (\Throwable $e) { /* table may not exist in some envs */ }
        }
    }

    public static function runCase(TestCase $t, string $caseName): array
    {
        $base = base_path('tests/Fixtures/electric_v1/'.$caseName);
        $inputs = [
            'allowance' => json_decode(file_get_contents($base.'/inputs/allowance.json'), true),
            'readings' => json_decode(file_get_contents($base.'/inputs/readings.json'), true),
            'attendance' => json_decode(file_get_contents($base.'/inputs/attendance.json'), true),
            'occupancy' => json_decode(file_get_contents($base.'/inputs/occupancy.json'), true),
            'adjustments' => json_decode(file_get_contents($base.'/inputs/adjustments.json'), true),
            'run' => json_decode(file_get_contents($base.'/inputs/run.json'), true),
        ];

        self::resetElectricV1State();

        self::postUpsertIfAny($t, '/api/electric-v1/input/allowance/upsert', $inputs['allowance']);
        self::postUpsertIfAny($t, '/api/electric-v1/input/readings/upsert', $inputs['readings']);
        self::postUpsertIfAny($t, '/api/electric-v1/input/attendance/upsert', $inputs['attendance']);
        self::postUpsertIfAny($t, '/api/electric-v1/input/occupancy/upsert', $inputs['occupancy']);
        self::postUpsertIfAny($t, '/api/electric-v1/input/adjustments/upsert', $inputs['adjustments']);

        $runRes = $t->postJson('/api/electric-v1/run', $inputs['run'])->assertStatus(200)->json();

        $q = http_build_query([
            'cycle_start' => $inputs['run']['cycle_start'],
            'cycle_end' => $inputs['run']['cycle_end'],
            'run_id' => $runRes['data']['run_id'] ?? null,
        ]);

        $bundle = $t->getJson('/api/electric-v1/outputs?'.$q)->assertStatus(200)->json('data');

        return [
            'run' => $runRes,
            'bundle' => $bundle,
            'expected' => [
                'final_outputs' => json_decode(file_get_contents($base.'/expected/final_outputs.json'), true),
                'drilldown_outputs' => json_decode(file_get_contents($base.'/expected/drilldown_outputs.json'), true),
                'exceptions' => json_decode(file_get_contents($base.'/expected/exceptions.json'), true),
                'run_summary' => json_decode(file_get_contents($base.'/expected/run_summary.json'), true),
            ],
            'base' => $base,
        ];
    }

    public static function writeDiff(string $caseBase, array $expected, array $actual): string
    {
        $outDir = storage_path('testing');
        if (!is_dir($outDir)) mkdir($outDir, 0777, true);
        $path = $outDir.'/electric_v1_diff_'.basename($caseBase).'.json';
        file_put_contents($path, json_encode(['expected'=>$expected,'actual'=>$actual], JSON_PRETTY_PRINT));
        return $path;
    }
}
