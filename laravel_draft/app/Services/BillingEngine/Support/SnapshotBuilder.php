<?php
namespace App\Services\BillingEngine\Support;

use Illuminate\Support\Facades\DB;

class SnapshotBuilder
{
    /**
     * DB se immutable snapshot banata hai — dono methods ke liye.
     * @return array ['rate','cycle_days','cycle_start','cycle_end','units'=>[...], 'issues'=>[...]]
     */
    public function build(string $cycleStart, string $cycleEnd, float $rate): array
    {
        $cycleDays = (new \DateTime($cycleStart))->diff(new \DateTime($cycleEnd))->days + 1;
        $issues = [];

        // 1. readings (prepared view — previous + current)
        $readings = DB::select(
            "SELECT unit_id, previous_reading, current_reading, reading_status
             FROM electric_v1_readings
             WHERE cycle_start_date = ? AND cycle_end_date = ?",
            [$cycleStart, $cycleEnd]
        );
        $consumptionByUnit = [];
        foreach ($readings as $r) {
            $u = trim((string)$r->unit_id);
            if (($r->reading_status ?? '') !== 'NORMAL') {
                $issues[] = ['unit'=>$u, 'code'=>'READING_STATUS_'.$r->reading_status];
                continue;
            }
            $consumptionByUnit[$u] = (float)$r->current_reading - (float)$r->previous_reading;
        }

        // 2. allowance (room-level, active only)
        $allowRows = DB::table('electric_v1_allowance')->where('is_active', 1)->get();

        // 3. occupancy (unit -> room -> employees) + active days
        $occ = DB::table('electric_v1_occupancy')->get();
        $monthDate = substr($cycleEnd, 0, 7).'-01';
        $daysByEmp = DB::table('electric_active_days_monthly')
            ->where('billing_month_date', $monthDate)
            ->pluck('active_days', 'company_id');

        // structure banao
        $units = [];
        foreach ($occ as $o) {
            $u = trim((string)$o->unit_id);
            $room = trim((string)($o->room_id ?? ''));
            $units[$u]['rooms'][$room]['employees'][] = [
                'company_id'  => (string)$o->company_id,
                'active_days' => (float)($daysByEmp[$o->company_id] ?? 0),
            ];
        }

        // allowance attach (unit_id + room_no match)
        foreach ($allowRows as $a) {
            $u = trim((string)$a->unit_id);
            $room = trim((string)($a->room_no ?? ''));
            if ($room !== '' && isset($units[$u]['rooms'][$room])) {
                $units[$u]['rooms'][$room]['allowance'] = (float)$a->free_electric;
            }
        }

        // consumption attach + missing flags
        foreach ($units as $u => &$unit) {
            if (!isset($consumptionByUnit[$u])) {
                $issues[] = ['unit'=>$u, 'code'=>'READING_MISSING'];
                unset($units[$u]);
                continue;
            }
            $unit['consumption'] = $consumptionByUnit[$u];
            foreach ($unit['rooms'] as $rn => $room) {
                if (!isset($room['allowance'])) {
                    $issues[] = ['unit'=>$u, 'room'=>$rn, 'code'=>'ALLOWANCE_MISSING'];
                }
            }
        }
        unset($unit);

        return [
            'rate'        => $rate,
            'cycle_days'  => $cycleDays,
            'cycle_start' => $cycleStart,
            'cycle_end'   => $cycleEnd,
            'units'       => $units,
            'issues'      => $issues,
        ];
    }
}
