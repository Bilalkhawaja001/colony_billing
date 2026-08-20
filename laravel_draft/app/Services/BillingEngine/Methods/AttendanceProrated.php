<?php
namespace App\Services\BillingEngine\Methods;

use App\Services\BillingEngine\Contracts\BillingMethod;

class AttendanceProrated implements BillingMethod
{
    public function code(): string { return 'ATTENDANCE_PRORATED'; }
    public function label(): string { return 'Attendance Prorated'; }

    /**
     * Snapshot format (equal-split jaisa + attendance + cycle_days):
     * 'cycle_days' => 31,
     * 'units' => [
     *   'WB-105' => [
     *     'consumption' => 608.0,
     *     'rooms' => [
     *       'WB-105-1' => [
     *         'allowance' => 250.0,
     *         'employees' => [
     *           ['company_id'=>'240105','active_days'=>31],
     *           ...
     *         ],
     *       ],
     *     ],
     *   ],
     * ]
     */
    public function compute(array $snapshot): array
    {
        $rate = (float) ($snapshot['rate'] ?? 0);
        $cycleDays = (int) ($snapshot['cycle_days'] ?? 0);
        $rows = [];
        $issues = [];
        $totalAmount = 0.0;

        if ($cycleDays <= 0) {
            return ['rows'=>[], 'issues'=>[['code'=>'CYCLE_DAYS_INVALID']], 'summary'=>['employees'=>0,'total_amount'=>0]];
        }

        foreach (($snapshot['units'] ?? []) as $unitId => $unit) {
            $consumption = (float) ($unit['consumption'] ?? 0);
            $rooms = $unit['rooms'] ?? [];

            // unit level: total free allowance (prorated per employee attendance) + total attendance
            $unitFree = 0.0;
            $unitAttendance = 0.0;
            foreach ($rooms as $roomNo => $room) {
                $emps = $room['employees'] ?? [];
                if (empty($emps)) continue;
                $allowance = (float) ($room['allowance'] ?? 0);
                $persons = count($emps);
                foreach ($emps as $e) {
                    $days = (float) ($e['active_days'] ?? 0);
                    // per-employee prorated free: allowance * (days/cycle) / persons
                    $unitFree += ($allowance * ($days / $cycleDays)) / $persons;
                    $unitAttendance += $days;
                }
            }

            if ($unitAttendance <= 0) {
                $issues[] = ['unit'=>$unitId, 'code'=>'NO_ATTENDANCE'];
                continue;
            }

            $unitBillable = max($consumption - $unitFree, 0.0);

            // employee share = unitBillable * (days / unitAttendance)
            $allEmps = [];
            foreach ($rooms as $roomNo => $room) {
                $roomEmployees = $room['employees'] ?? [];
                $persons = count($roomEmployees);
                foreach ($roomEmployees as $e) {
                    $days = (float)($e['active_days'] ?? 0);
                    $allowance = (float)($room['allowance'] ?? 0);
                    $allEmps[] = [
                        'room'=>$roomNo,
                        'company_id'=>$e['company_id'],
                        'days'=>$days,
                        'allowance'=>$allowance,
                        'room_persons'=>$persons,
                        'eligible_units'=>($allowance * ($days / $cycleDays)) / $persons,
                        'emp_used_units'=>$consumption * ($days / $unitAttendance),
                    ];
                }
            }

            $empCount = count($allEmps);
            $allocated = 0.0;
            foreach ($allEmps as $i => $e) {
                $isLast = ($i === $empCount - 1);
                $share = $unitBillable * ($e['days'] / $unitAttendance);
                $units = $isLast ? ($unitBillable - $allocated) : round($share, 4);
                $allocated += $units;
                $amount = round($units * $rate, 2);
                $totalAmount += $amount;

                $rows[] = [
                    'company_id'    => $e['company_id'],
                    'unit_id'       => $unitId,
                    'room_no'       => $e['room'],
                    'room_units'    => round($consumption, 4),
                    'allowance'     => $e['allowance'],
                    'billable_units'=> round($units, 4),
                    'rate'          => $rate,
                    'amount'        => $amount,
                    'room_persons'  => $e['room_persons'],
                    'emp_used_units'=> round($e['emp_used_units'], 4),
                    'eligible_units'=> round($e['eligible_units'], 4),
                    'unit_used_elec'=> round($consumption, 4),
                    'unit_total_attendance' => round($unitAttendance, 4),
                    'active_days'   => $e['days'],
                    'employee_attendance_in_unit' => $e['days'],
                ];
            }
        }

        return [
            'rows'    => $rows,
            'issues'  => $issues,
            'summary' => ['employees'=>count($rows), 'total_amount'=>round($totalAmount, 2)],
        ];
    }
}
