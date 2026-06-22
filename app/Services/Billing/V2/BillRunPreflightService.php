<?php

namespace App\Services\Billing\V2;

use App\Models\BillRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class BillRunPreflightService
{
    public function evaluate(BillRun $run): array
    {
        $checks = [];

        $this->checkTable($checks, 'bill_runs', 'Bill run table');
        $this->checkTable($checks, 'audit_log', 'Audit trail table');
        $this->checkTable($checks, 'electric_v1_hr_attendance', 'Attendance source');
        $this->checkTable($checks, 'electric_v1_readings', 'Reading source');
        $this->checkTable($checks, 'electric_v1_room_allowance', 'Room allowance source');
        $this->checkTable($checks, 'util_monthly_rates_config', 'Rate source');

        $this->checkPeriodRate($checks, $run);
        $this->checkAttendance($checks, $run);
        $this->checkReadings($checks, $run);
        $this->checkRoomAllowance($checks);

        $summary = $this->summary($checks);

        return [
            'bill_run_id' => $run->id,
            'month_cycle' => $run->month_cycle,
            'cycle_start_date' => $this->dateString($run->cycle_start_date),
            'cycle_end_date' => $this->dateString($run->cycle_end_date),
            'can_generate' => $summary['stop'] === 0,
            'summary' => $summary,
            'checks' => $checks,
        ];
    }

    public function saveResult(BillRun $run, array $result): int
    {
        DB::table('bill_run_preflight_checks')->where('bill_run_id', $run->id)->delete();

        $count = 0;
        foreach ($result['checks'] ?? [] as $check) {
            DB::table('bill_run_preflight_checks')->insert([
                'bill_run_id' => $run->id,
                'check_code' => $check['code'],
                'severity' => $check['severity'],
                'status' => $check['status'],
                'title' => $check['title'],
                'message' => $check['message'] ?? null,
                'entity_type' => $check['entity_type'] ?? null,
                'entity_id' => $check['entity_id'] ?? null,
                'source_table' => $check['source_table'] ?? null,
                'meta_json' => json_encode($check['meta'] ?? [], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    private function checkTable(array &$checks, string $table, string $title): void
    {
        $exists = Schema::hasTable($table);
        $checks[] = [
            'code' => 'table_'.$table,
            'severity' => $exists ? 'info' : 'stop',
            'status' => $exists ? 'pass' : 'fail',
            'title' => $title,
            'message' => $exists ? 'Ready' : 'Missing table',
            'source_table' => $table,
        ];
    }

    private function checkPeriodRate(array &$checks, BillRun $run): void
    {
        if (!Schema::hasTable('util_monthly_rates_config')) {
            return;
        }

        $count = DB::table('util_monthly_rates_config')->where('month_cycle', $run->month_cycle)->count();
        $checks[] = [
            'code' => 'period_rate',
            'severity' => $count > 0 ? 'info' : 'stop',
            'status' => $count > 0 ? 'pass' : 'fail',
            'title' => 'Monthly rate configured',
            'message' => $count > 0 ? 'Rate row found' : 'Rate row missing for month',
            'source_table' => 'util_monthly_rates_config',
            'meta' => ['count' => $count],
        ];
    }

    private function checkAttendance(array &$checks, BillRun $run): void
    {
        if (!Schema::hasTable('electric_v1_hr_attendance')) {
            return;
        }

        $q = DB::table('electric_v1_hr_attendance')
            ->whereDate('cycle_start_date', $this->dateString($run->cycle_start_date))
            ->whereDate('cycle_end_date', $this->dateString($run->cycle_end_date));

        $count = (clone $q)->count();
        $over = (clone $q)->where('attendance_days', '>', $run->cycle_days)->count();

        $checks[] = [
            'code' => 'attendance_loaded',
            'severity' => $count > 0 ? 'info' : 'stop',
            'status' => $count > 0 ? 'pass' : 'fail',
            'title' => 'Attendance loaded',
            'message' => $count > 0 ? 'Attendance rows found' : 'No attendance rows found for cycle',
            'source_table' => 'electric_v1_hr_attendance',
            'meta' => ['count' => $count],
        ];

        $checks[] = [
            'code' => 'attendance_days_within_cycle',
            'severity' => $over === 0 ? 'info' : 'stop',
            'status' => $over === 0 ? 'pass' : 'fail',
            'title' => 'Attendance days within cycle',
            'message' => $over === 0 ? 'No over-cycle attendance found' : 'Attendance exceeds cycle days',
            'source_table' => 'electric_v1_hr_attendance',
            'meta' => ['over_cycle_count' => $over],
        ];
    }

    private function checkReadings(array &$checks, BillRun $run): void
    {
        if (!Schema::hasTable('electric_v1_readings')) {
            return;
        }

        $q = DB::table('electric_v1_readings')
            ->whereDate('cycle_start_date', $this->dateString($run->cycle_start_date))
            ->whereDate('cycle_end_date', $this->dateString($run->cycle_end_date));

        $count = (clone $q)->count();
        $reverse = (clone $q)->whereColumn('current_reading', '<', 'previous_reading')->count();

        $checks[] = [
            'code' => 'readings_loaded',
            'severity' => $count > 0 ? 'info' : 'stop',
            'status' => $count > 0 ? 'pass' : 'fail',
            'title' => 'Readings loaded',
            'message' => $count > 0 ? 'Reading rows found' : 'No reading rows found for cycle',
            'source_table' => 'electric_v1_readings',
            'meta' => ['count' => $count],
        ];

        $checks[] = [
            'code' => 'readings_not_reversed',
            'severity' => $reverse === 0 ? 'info' : 'stop',
            'status' => $reverse === 0 ? 'pass' : 'fail',
            'title' => 'Readings not reversed',
            'message' => $reverse === 0 ? 'No reversed readings found' : 'Current reading is lower than previous reading',
            'source_table' => 'electric_v1_readings',
            'meta' => ['reverse_count' => $reverse],
        ];
    }

    private function checkRoomAllowance(array &$checks): void
    {
        if (!Schema::hasTable('electric_v1_room_allowance')) {
            return;
        }

        $count = DB::table('electric_v1_room_allowance')->where('is_active', 1)->count();
        $checks[] = [
            'code' => 'room_allowance_loaded',
            'severity' => $count > 0 ? 'info' : 'stop',
            'status' => $count > 0 ? 'pass' : 'fail',
            'title' => 'Room allowance loaded',
            'message' => $count > 0 ? 'Active allowance rows found' : 'No active room allowance rows found',
            'source_table' => 'electric_v1_room_allowance',
            'meta' => ['count' => $count],
        ];
    }

    private function summary(array $checks): array
    {
        $summary = ['pass' => 0, 'fail' => 0, 'stop' => 0, 'warn' => 0, 'info' => 0];
        foreach ($checks as $check) {
            $summary[$check['status']] = ($summary[$check['status']] ?? 0) + 1;
            $summary[$check['severity']] = ($summary[$check['severity']] ?? 0) + 1;
        }

        return $summary;
    }

    private function dateString(mixed $date): string
    {
        return $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : substr((string) $date, 0, 10);
    }
}
