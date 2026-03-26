<?php

namespace App\Services\ElectricV1\Domain;

class AllocationEngine
{
    public static function allocate(string $unitId, string $residenceType, float $grossUnits, array $roomAllocations, array $houseResponsible): array
    {
        $res = strtoupper(trim($residenceType));
        if ($res === 'HOUSE') {
            $resp = array_values(array_unique(array_filter($houseResponsible)));
            if (count($resp) !== 1) {
                return ['allocations' => [], 'issues' => [['code' => 'E_HOUSE_RESP_NOT_SINGLE', 'message' => 'HOUSE unit must resolve to exactly one responsible employee', 'severity' => 'ERROR', 'unit_id' => $unitId]]];
            }
            return ['allocations' => [[
                'company_id' => $resp[0], 'unit_id' => $unitId, 'residence_type' => 'HOUSE', 'employee_attendance_in_unit' => 0.0, 'allocated_gross_units' => round($grossUnits,4)
            ]], 'issues' => []];
        }

        if ($res !== 'ROOM') {
            return ['allocations' => [], 'issues' => [['code' => 'E_ALLOW_INVALID_TYPE', 'message' => 'Unsupported residence type', 'severity' => 'ERROR', 'unit_id' => $unitId]]];
        }

        $byEmp = [];
        foreach ($roomAllocations as $r) {
            if (($r['unit_id'] ?? '') !== $unitId) continue;
            $cid = (string)($r['company_id'] ?? '');
            if ($cid === '') continue;
            $byEmp[$cid] = ($byEmp[$cid] ?? 0.0) + (float)($r['attendance_days'] ?? 0);
        }
        $totalAtt = array_sum($byEmp);
        if ($grossUnits > 0 && $totalAtt <= 0) {
            return ['allocations' => [], 'issues' => [['code' => 'E_UNIT_ZERO_ATT_WITH_CONS', 'message' => 'ROOM unit has consumption but zero attendance', 'severity' => 'ERROR', 'unit_id' => $unitId]]];
        }
        if ($totalAtt <= 0) return ['allocations' => [], 'issues' => []];

        ksort($byEmp);
        $allocs = [];
        $running = 0.0;
        $keys = array_keys($byEmp);
        foreach ($keys as $i => $cid) {
            $att = $byEmp[$cid];
            if ($i === count($keys)-1) {
                $alloc = round($grossUnits - $running, 4);
            } else {
                $alloc = round(($att/$totalAtt) * $grossUnits, 4);
                $running += $alloc;
            }
            $allocs[] = ['company_id'=>$cid,'unit_id'=>$unitId,'residence_type'=>'ROOM','employee_attendance_in_unit'=>round($att,4),'allocated_gross_units'=>$alloc];
        }
        return ['allocations' => $allocs, 'issues' => []];
    }
}
