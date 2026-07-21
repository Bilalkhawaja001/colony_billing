<?php
namespace App\Services\BillingEngine\Methods;

use App\Services\BillingEngine\Contracts\BillingMethod;

class OccupiedRoomEqualSplit implements BillingMethod
{
    public function code(): string { return 'OCCUPIED_ROOM_EQUAL_SPLIT'; }
    public function label(): string { return 'Occupied Room Equal Split'; }

    /**
     * Snapshot format:
     * [
     *   'rate' => 13.92,
     *   'units' => [
     *     'WB-105' => [
     *       'consumption' => 608.0,
     *       'rooms' => [
     *         'WB-105-1' => ['allowance' => 250.0, 'employees' => ['240105','240106']],
     *         ...
     *       ],
     *     ],
     *   ],
     * ]
     */
    public function compute(array $snapshot): array
    {
        $rate = (float) ($snapshot['rate'] ?? 0);
        $rows = [];
        $issues = [];
        $totalAmount = 0.0;

        foreach (($snapshot['units'] ?? []) as $unitId => $unit) {
            $consumption = (float) ($unit['consumption'] ?? 0);
            $rooms = $unit['rooms'] ?? [];

            // sirf occupied rooms; attendance 0 wale employees exclude
            $rooms = array_map(function($r){
                $r['employees'] = array_values(array_filter($r['employees'] ?? [], function($e){
                    return !is_array($e) || ((float)($e['active_days'] ?? 1)) > 0;
                }));
                return $r;
            }, $rooms);
            $occupied = array_filter($rooms, fn($r) => !empty($r['employees']));
            $occCount = count($occupied);

            if ($occCount === 0) {
                $issues[] = ['unit' => $unitId, 'code' => 'NO_OCCUPIED_ROOMS'];
                continue;
            }

            $perRoom = $consumption / $occCount;

            foreach ($occupied as $roomNo => $room) {
                $allowance = (float) ($room['allowance'] ?? 0);
                if ($allowance <= 0) {
                    $issues[] = ['unit' => $unitId, 'room' => $roomNo, 'code' => 'ALLOWANCE_MISSING'];
                    continue;
                }

                $billable = max($perRoom - $allowance, 0.0);
                $emps = $room['employees'];
                $empCount = count($emps);
                $perEmp = $billable / $empCount;

                // remainder handling: last employee ko rounding ka farq
                $allocated = 0.0;
                foreach ($emps as $i => $emp) {
                    $companyId = is_array($emp) ? $emp['company_id'] : $emp;
                    $isLast = ($i === $empCount - 1);
                    $units = $isLast ? ($billable - $allocated) : round($perEmp, 4);
                    $allocated += $units;
                    $amount = round($units * $rate, 2);
                    $totalAmount += $amount;

                    $rows[] = [
                        'company_id'    => $companyId,
                        'unit_id'       => $unitId,
                        'room_no'       => $roomNo,
                        'room_units'    => round($perRoom, 4),
                        'allowance'     => $allowance,
                        'billable_units'=> round($units, 4),
                        'rate'          => $rate,
                        'amount'        => $amount,
                    ];
                }
            }
        }

        return [
            'rows'    => $rows,
            'issues'  => $issues,
            'summary' => [
                'employees'    => count($rows),
                'total_amount' => round($totalAmount, 2),
            ],
        ];
    }
}
