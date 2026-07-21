<?php
namespace App\Services\Billing\ControlRoom;

use Illuminate\Support\Facades\DB;

class WizardStepService
{
    public function steps(string $cycleStart, string $cycleEnd, ?string $monthCycle = null): array
    {
        $monthDate = substr($cycleEnd, 0, 7).'-01';

        // 1. Readings
        $readingRows = DB::table('electric_v1_readings')
            ->where('cycle_start_date', $cycleStart)->where('cycle_end_date', $cycleEnd)->count();
        $badReadings = DB::table('electric_v1_readings')
            ->where('cycle_start_date', $cycleStart)->where('cycle_end_date', $cycleEnd)
            ->where(function ($q) {
                $q->where('reading_status', '!=', 'NORMAL')
                  ->orWhereRaw('current_reading < previous_reading');
            })->count();

        // 2. Attendance
        $attRows = DB::table('electric_active_days_monthly')->where('billing_month_date', $monthDate)->count();
        $zeroAtt = DB::table('electric_active_days_monthly')
            ->where('billing_month_date', $monthDate)->where('active_days', '<=', 0)->count();

        // 3. Allowances
        $occRooms = DB::table('electric_v1_occupancy')
            ->select('unit_id', 'room_id')->distinct()->get();
        $missingAllow = 0;
        foreach ($occRooms as $r) {
            $has = DB::table('electric_v1_allowance')
                ->where('unit_id', $r->unit_id)->where('room_no', $r->room_id)
                ->where('is_active', 1)->where('free_electric', '>', 0)->exists();
            if (!$has) { $missingAllow++; }
        }

        // 4. Rate
        $rate = 0.0;
        try {
            $rate = (float) (DB::table('util_monthly_rates_config')
                ->where('month_cycle', $monthCycle)->value('elec_rate') ?? 0);
        } catch (\Throwable $e) { $rate = 0.0; }

        return [
            [
                'key' => 'readings', 'title' => 'Meter Readings',
                'ok' => $readingRows > 0 && $badReadings === 0,
                'count' => $readingRows, 'issues' => $badReadings,
                'detail' => $readingRows === 0 ? 'No readings found for this cycle.' : ($badReadings > 0 ? $badReadings.' reading(s) abnormal or reversed.' : 'All readings look normal.'),
                'fix_url' => url('meters-readings'),
            ],
            [
                'key' => 'attendance', 'title' => 'Attendance / Active Days',
                'ok' => $attRows > 0,
                'count' => $attRows, 'issues' => $zeroAtt,
                'detail' => $attRows === 0 ? 'No attendance rows for '.$monthDate.'.' : $zeroAtt.' employee(s) with zero days (will be excluded).',
                'fix_url' => url('active-days-monthly'),
            ],
            [
                'key' => 'allowances', 'title' => 'Room Allowances',
                'ok' => $missingAllow === 0,
                'count' => count($occRooms), 'issues' => $missingAllow,
                'detail' => $missingAllow === 0 ? 'All occupied rooms have an allowance.' : $missingAllow.' occupied room(s) have no allowance.',
                'fix_url' => url('allowances'),
            ],
            [
                'key' => 'rate', 'title' => 'Electric Rate',
                'ok' => $rate > 0,
                'count' => $rate, 'issues' => $rate > 0 ? 0 : 1,
                'detail' => $rate > 0 ? 'Rate: '.number_format($rate, 2).' per unit.' : 'No rate configured for '.$monthCycle.'.',
                'fix_url' => url('monthly-rates/config'),
            ],
        ];
    }
}
