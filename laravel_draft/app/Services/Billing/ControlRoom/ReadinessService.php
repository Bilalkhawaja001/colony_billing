<?php

namespace App\Services\Billing\ControlRoom;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReadinessService
{
    private array $tableExistsMemo = [];
    public function summary(?string $requestedMonthCycle = null): array
    {
        $blockers = [];
        $tables = $this->tableMap();

        $tableStatus = [];
        foreach ($tables as $table) {
            $tableStatus[$table] = $this->tableStatus($table);
        }

        $cycle = $this->resolveCycle($requestedMonthCycle);
        $monthCycle = $cycle['month_cycle'];
        $cycleStart = $cycle['cycle_start_date'];
        $cycleEnd = $cycle['cycle_end_date'];
        $billingMonthDate = $cycle['billing_month_date'];

        if (!$monthCycle) {
            $blockers[] = $this->issue('NO_MONTH_CYCLE', 'Month cycle missing', 'util_month_cycle me koi billing month cycle nahi mila.', 'critical', 'util_month_cycle');
        }

        if (!$cycleStart || !$cycleEnd) {
            $blockers[] = $this->issue('NO_CYCLE_DATES', 'Cycle dates missing', 'Selected month cycle ke start/end dates missing hain.', 'critical', 'util_month_cycle');
        }

        $stats = [
            'month_cycle' => $monthCycle ?: '-',
            'cycle_start_date' => $cycleStart ?: '-',
            'cycle_end_date' => $cycleEnd ?: '-',
            'cycle_days' => $cycle['cycle_days'] ?: '-',
            'active_employees' => $this->countActiveEmployees(),
            'active_residences' => $this->countActiveResidences($cycleStart, $cycleEnd),
            'active_meters' => $this->countActiveMeters(),
            'active_days_rows' => $this->countActiveDaysRows($billingMonthDate),
            'meter_readings_total' => $this->countRows('util_meter_readings'),
            'current_readings' => $cycleEnd ? $this->countMeterReadingsOnDate($cycleEnd) : 0,
            'previous_readings' => $cycleStart ? $this->countMeterReadingsOnOrBefore(Carbon::parse($cycleStart)->subDay()->toDateString()) : 0,
            'room_allowance_rows' => $this->countRoomAllowanceRows(),
            'electric_rate' => $this->electricRate($monthCycle),
            'bill_runs' => $this->countBillRuns($monthCycle),
            'latest_run_id' => '-',
            'latest_run_status' => '-',
        ];

        $latestRun = $this->latestBillRun($monthCycle);
        if ($latestRun) {
            $stats['latest_run_id'] = $latestRun->run_uuid ?? $latestRun->id ?? '-';
            $stats['latest_run_status'] = $latestRun->status ?? '-';
        }

        if ($stats['active_employees'] <= 0) {
            $blockers[] = $this->issue('NO_ACTIVE_EMPLOYEES', 'Active employees missing', 'employees_master me active employees count zero aa raha hai.', 'critical', 'employees_master');
        }

        if ($stats['active_residences'] <= 0) {
            $blockers[] = $this->issue('NO_ACTIVE_RESIDENCE', 'Residence assignments missing', 'employee_residence_assignments me selected cycle ke active rows nahi milay.', 'critical', 'employee_residence_assignments');
        }

        if ($stats['active_meters'] <= 0) {
            $blockers[] = $this->issue('NO_ACTIVE_METERS', 'Active meters missing', 'util_meter_unit me active electric meters missing hain.', 'critical', 'util_meter_unit');
        }

        if ($cycleEnd && $stats['current_readings'] <= 0) {
            $blockers[] = $this->issue('NO_CURRENT_READINGS', 'Current readings missing', "Cycle end date {$cycleEnd} par meter readings nahi mil rahi.", 'critical', 'util_meter_readings');
        }

        if ($cycleStart && $stats['previous_readings'] <= 0) {
            $previousDate = Carbon::parse($cycleStart)->subDay()->toDateString();
            $blockers[] = $this->issue('NO_PREVIOUS_READINGS', 'Previous readings missing', "Cycle start se pehle {$previousDate} tak previous readings nahi mil rahi.", 'critical', 'util_meter_readings');
        }

        if ($billingMonthDate && $stats['active_days_rows'] <= 0) {
            $blockers[] = $this->issue('NO_ACTIVE_DAYS', 'Active days missing', "electric_active_days_monthly me {$billingMonthDate} ke rows nahi milay.", 'critical', 'electric_active_days_monthly');
        }

        if ($stats['room_allowance_rows'] <= 0) {
            $blockers[] = $this->issue('NO_ROOM_ALLOWANCE', 'Room allowance missing', 'electric_v1_room_allowance me active allowance rows missing hain.', 'critical', 'electric_v1_room_allowance');
        }

        if (!$stats['electric_rate'] || (float) $stats['electric_rate'] <= 0) {
            $blockers[] = $this->issue('NO_ELECTRIC_RATE', 'Electric rate missing', "util_monthly_rates_config me {$monthCycle} ka electric rate missing/zero hai.", 'critical', 'util_monthly_rates_config');
        }

        return [
            'isReady' => count($blockers) === 0,
            'mode' => count($blockers) === 0 ? 'READY_FOR_GENERATE_WIRING' : 'BLOCKED_BY_REAL_DATA',
            'month' => $monthCycle,
            'cycle' => $cycle,
            'lastChecked' => now()->format('Y-m-d H:i:s'),
            'stats' => $stats,
            'blockers' => $blockers,
            'warnings' => [],
            'tables' => $tableStatus,
        ];
    }

    private function tableMap(): array
    {
        return [
            'employees_master',
            'employee_residence_assignments',
            'util_meter_unit',
            'util_meter_readings',
            'util_month_cycle',
            'util_monthly_rates_config',
            'electric_active_days_monthly',
            'electric_v1_readings',
            'electric_v1_room_allowance',
            'electric_v1_output_employee_final',
            'electric_v1_output_employee_unit_drilldown',
            'bill_runs',
            'bill_run_preflight_checks',
            'bill_run_snapshots',
        ];
    }

    private function tableStatus(string $table): array
    {
        try {
            $exists = $this->tableExists($table);

            return [
                'exists' => $exists,
                'count' => $exists ? DB::table($table)->count() : null,
            ];
        } catch (\Throwable $e) {
            return [
                'exists' => false,
                'count' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function resolveCycle(?string $requestedMonthCycle): array
    {
        $row = null;

        try {
            if ($this->tableExists('util_month_cycle')) {
                $q = DB::table('util_month_cycle');

                if ($requestedMonthCycle) {
                    $row = (clone $q)->where('month_cycle', $requestedMonthCycle)->first();
                }

                if (!$row) {
                    $row = (clone $q)
                        ->orderByRaw("CASE WHEN state = 'OPEN' THEN 0 ELSE 1 END")
                        ->orderByDesc('cycle_start_date')
                        ->orderByDesc('month_cycle')
                        ->first();
                }
            }
        } catch (\Throwable $e) {
            $row = null;
        }

        $monthCycle = $row->month_cycle ?? $requestedMonthCycle;
        $start = $row->cycle_start_date ?? null;
        $end = $row->cycle_end_date ?? null;

        if ((!$start || !$end) && $monthCycle) {
            [$inferredStart, $inferredEnd] = $this->inferDatesFromMonthCycle($monthCycle);
            $start = $start ?: $inferredStart;
            $end = $end ?: $inferredEnd;
        }

        $days = null;
        if ($start && $end) {
            $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        }

        return [
            'month_cycle' => $monthCycle,
            'cycle_start_date' => $start,
            'cycle_end_date' => $end,
            'cycle_days' => $days,
            'billing_month_date' => $this->billingMonthDate($monthCycle, $start),
        ];
    }

    private function inferDatesFromMonthCycle(string $monthCycle): array
    {
        try {
            if (preg_match('/^\d{4}-\d{2}$/', $monthCycle)) {
                $start = Carbon::parse($monthCycle . '-01');
                return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
            }

            if (preg_match('/^\d{2}-\d{4}$/', $monthCycle)) {
                [$m, $y] = explode('-', $monthCycle);
                $start = Carbon::parse($y . '-' . $m . '-01');
                return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
            }
        } catch (\Throwable $e) {
            return [null, null];
        }

        return [null, null];
    }

    private function billingMonthDate(?string $monthCycle, ?string $cycleStart): ?string
    {
        if ($monthCycle) {
            [$start] = $this->inferDatesFromMonthCycle($monthCycle);
            if ($start) {
                return Carbon::parse($start)->startOfMonth()->toDateString();
            }
        }

        return $cycleStart ? Carbon::parse($cycleStart)->startOfMonth()->toDateString() : null;
    }

    private function countRows(string $table): int
    {
        try {
            return $this->tableExists($table) ? DB::table($table)->count() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countActiveEmployees(): int
    {
        try {
            if (!$this->tableExists('employees_master')) {
                return 0;
            }

            return DB::table('employees_master')
                ->where(function ($q) {
                    $q->where('active', 'Yes')
                        ->orWhere('active', '1')
                        ->orWhere('active', 1)
                        ->orWhereRaw("UPPER(active) = 'ACTIVE'");
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countActiveResidences(?string $start, ?string $end): int
    {
        try {
            if (!$this->tableExists('employee_residence_assignments')) {
                return 0;
            }

            $q = DB::table('employee_residence_assignments')->whereRaw("UPPER(status) = 'ACTIVE'");

            if ($start && $end) {
                $q->where('start_date', '<=', $end)
                    ->where(function ($qq) use ($start) {
                        $qq->whereNull('end_date')->orWhere('end_date', '>=', $start);
                    });
            }

            return $q->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countActiveMeters(): int
    {
        try {
            if (!$this->tableExists('util_meter_unit')) {
                return 0;
            }

            return DB::table('util_meter_unit')
                ->where('is_active', 1)
                ->where(function ($q) {
                    $q->whereNull('meter_type')
                        ->orWhereRaw("UPPER(meter_type) NOT LIKE '%GYSER%'");
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countMeterReadingsOnDate(string $date): int
    {
        try {
            return $this->tableExists('util_meter_readings')
                ? DB::table('util_meter_readings')->whereDate('reading_date', $date)->count()
                : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countMeterReadingsOnOrBefore(string $date): int
    {
        try {
            return $this->tableExists('util_meter_readings')
                ? DB::table('util_meter_readings')->whereDate('reading_date', '<=', $date)->count()
                : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countActiveDaysRows(?string $billingMonthDate): int
    {
        try {
            return $billingMonthDate && $this->tableExists('electric_active_days_monthly')
                ? DB::table('electric_active_days_monthly')->whereDate('billing_month_date', $billingMonthDate)->count()
                : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function countRoomAllowanceRows(): int
    {
        try {
            return $this->tableExists('electric_v1_room_allowance')
                ? DB::table('electric_v1_room_allowance')->where('is_active', 1)->count()
                : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function electricRate(?string $monthCycle): ?float
    {
        try {
            if (!$monthCycle || !$this->tableExists('util_monthly_rates_config')) {
                return null;
            }

            $rate = DB::table('util_monthly_rates_config')
                ->where('month_cycle', $monthCycle)
                ->value('elec_rate');

            return $rate === null ? null : (float) $rate;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function latestBillRun(?string $monthCycle): ?object
    {
        try {
            if (!$this->tableExists('bill_runs')) {
                return null;
            }

            $q = DB::table('bill_runs')->orderByDesc('id');

            if ($monthCycle) {
                $q->where('month_cycle', $monthCycle);
            }

            return $q->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function countBillRuns(?string $monthCycle): int
    {
        try {
            if (!$this->tableExists('bill_runs')) {
                return 0;
            }

            $q = DB::table('bill_runs');

            if ($monthCycle) {
                $q->where('month_cycle', $monthCycle);
            }

            return $q->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function issue(string $code, string $title, string $message, string $severity, ?string $sourceTable = null): array
    {
        return [
            'code' => $code,
            'title' => $title,
            'message' => $message,
            'severity' => $severity,
            'source_table' => $sourceTable,
        ];
    }

    private function tableExists(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsMemo)) {
            $this->tableExistsMemo[$table] = Schema::hasTable($table);
        }

        return $this->tableExistsMemo[$table];
    }


}
