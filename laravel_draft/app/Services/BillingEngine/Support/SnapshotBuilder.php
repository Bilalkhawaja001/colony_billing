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
            if (!in_array(strtoupper(trim((string) ($r->reading_status ?? ''))), ['OK', 'NORMAL'], true)) {
                $issues[] = ['unit'=>$u, 'code'=>'READING_STATUS_'.$r->reading_status];
                continue;
            }
            $consumptionByUnit[$u] = (float)$r->current_reading - (float)$r->previous_reading;
        }

        // 2. allowance sources
        // Dedicated room table is authoritative.
        // Legacy table is fallback for non-migrated rooms / unit-level allowance.
        $roomAllowRows = DB::table('electric_v1_room_allowance')->get();
        $legacyAllowRows = DB::table('electric_v1_allowance')->where('is_active', 1)->get();

        $roomAllowByUnit = [];
        foreach ($roomAllowRows as $a) {
            $u = trim((string)$a->unit_id);
            $room = trim((string)$a->room_no);

            if ($u === '' || $room === '') {
                continue;
            }

            $roomAllowByUnit[$u][$room] = [
                'allowance' => (float)$a->room_free_allowance,
                'active' => (bool)$a->is_active,
            ];
        }

        $legacyRoomAllowByUnit = [];
        $unitFallbackAllow = [];

        foreach ($legacyAllowRows as $a) {
            $u = trim((string)$a->unit_id);
            $room = trim((string)($a->room_no ?? ''));

            if ($u === '') {
                continue;
            }

            if ($room === '') {
                $unitFallbackAllow[$u] = (float)$a->free_electric;
            } else {
                $legacyRoomAllowByUnit[$u][$room] = (float)$a->free_electric;
            }
        }

        // 3. occupancy (unit -> room -> employees) + active days
        // Only this cycle's occupancy. Without this filter every past cycle's
        // rows are pulled in, so an employee who changed rooms appears once
        // per historical room and is billed multiple times.
        /*
         * Residence history is authoritative for billing occupancy.
         *
         * Include every assignment that overlaps the selected billing cycle.
         * This correctly handles:
         * - current residents
         * - residents closed during the cycle
         * - employees shifted between rooms/units
         *
         * Assignments ending before cycle start are excluded automatically.
         */
        $occ = DB::table('employee_residence_assignments')
            ->whereDate('start_date', '<=', $cycleEnd)
            ->where(function ($q) use ($cycleStart) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $cycleStart);
            })
            ->whereRaw("UPPER(TRIM(unit_id)) NOT IN ('OUTSIDE','OUTSIDE COLONY')")
            ->get([
                'company_id',
                'unit_id',
                DB::raw('room_no as room_id'),
                'start_date',
                'end_date',
            ]);
        $monthDate = substr($cycleEnd, 0, 7).'-01';

        $daysByEmp = DB::table('electric_active_days_monthly')
            ->where('billing_month_date', $monthDate)
            ->pluck('active_days', 'company_id');

        $units = [];

        foreach ($occ as $o) {
            $u = trim((string)$o->unit_id);
            $room = trim((string)($o->room_id ?? ''));

            // Blank room + exactly one defined room = safely map to that room.
            if (
                $room === ''
                && isset($roomAllowByUnit[$u])
                && count($roomAllowByUnit[$u]) === 1
            ) {
                $room = array_key_first($roomAllowByUnit[$u]);
            }

            $units[$u]['rooms'][$room]['employees'][] = [
                'company_id'  => (string)$o->company_id,
                'active_days' => (float)($daysByEmp[$o->company_id] ?? 0),
            ];
        }

        // Attach allowance.
        foreach ($units as $u => &$unit) {
            foreach ($unit['rooms'] as $rn => &$room) {

                // Dedicated room record exists: it is authoritative.
                if ($rn !== '' && isset($roomAllowByUnit[$u][$rn])) {
                    if ($roomAllowByUnit[$u][$rn]['active']) {
                        $room['allowance'] = $roomAllowByUnit[$u][$rn]['allowance'];
                    }
                    continue;
                }

                // Legacy fallback only if room does not exist in dedicated table.
                if ($rn !== '' && isset($legacyRoomAllowByUnit[$u][$rn])) {
                    $room['allowance'] = $legacyRoomAllowByUnit[$u][$rn];
                    continue;
                }

                // Blank room uses active unit-level legacy allowance.
                if ($rn === '' && isset($unitFallbackAllow[$u])) {
                    $room['allowance'] = $unitFallbackAllow[$u];
                }
            }
            unset($room);
        }
        unset($unit);

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
