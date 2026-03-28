<?php

namespace App\Services\ElectricV1\Domain;

class AttendanceAllocator
{
    public static function buildEligible(array $attendanceRows, array $employeeByCompany, string $cycleStart, string $cycleEnd): array
    {
        $issues = [];
        $eligible = [];
        $skip = [];
        $seen = [];
        $cycleDays = (strtotime($cycleEnd) - strtotime($cycleStart)) / 86400 + 1;

        foreach ($attendanceRows as $r) {
            $cid = trim((string)($r['company_id'] ?? ''));
            if ($cid === '') continue;
            $k = $cid.'|'.$cycleStart.'|'.$cycleEnd;
            if (isset($seen[$k])) { $issues[] = ['code'=>'E_HR_DUP','message'=>'Duplicate HR attendance row','severity'=>'ERROR','company_id'=>$cid]; $skip[$cid]=true; continue; }
            $seen[$k]=true;

            if (!isset($employeeByCompany[$cid])) { $issues[] = ['code'=>'E_EMP_NOT_ELIGIBLE','message'=>'Employee missing in master','severity'=>'ERROR','company_id'=>$cid]; $skip[$cid]=true; continue; }
            $att = is_numeric($r['attendance_days'] ?? null) ? (float)$r['attendance_days'] : null;
            if ($att === null || $att < 0) { $issues[] = ['code'=>'E_HR_INVALID','message'=>'Invalid attendance','severity'=>'ERROR','company_id'=>$cid]; $skip[$cid]=true; continue; }
            if ($att > $cycleDays) { $issues[] = ['code'=>'W_HR_CAPPED','message'=>'Attendance capped to cycle days','severity'=>'WARNING','company_id'=>$cid]; $att = $cycleDays; }
            $eligible[$cid] = $att;
        }

        return ['eligible'=>$eligible,'issues'=>$issues,'room_skip'=>$skip];
    }

    public static function allocateRoom(array $eligible, array $occupancyRows, array $skip): array
    {
        $issues = [];
        $alloc = [];
        foreach ($eligible as $cid => $att) {
            if (!empty($skip[$cid])) continue;
            $rows = array_values(array_filter($occupancyRows, fn($x) => (($x['company_id'] ?? '') === $cid)));
            if (count($rows) === 0) { $issues[] = ['code'=>'E_EMP_NO_VALID_ROOM','message'=>'No valid room mapping','severity'=>'ERROR','company_id'=>$cid]; continue; }

            $unitDays=[]; $roomDays=[]; $total=0.0;
            foreach ($rows as $r) {
                $unit = (string)($r['unit_id'] ?? ''); $room = (string)($r['room_id'] ?? '');
                if ($unit===''||$room==='') continue;
                $from = strtotime((string)($r['from_date'] ?? '')); $to = strtotime((string)($r['to_date'] ?? ''));
                if (!$from || !$to || $from>$to) continue;
                $days = (($to-$from)/86400)+1;
                if ($days<=0) continue;
                $total += $days;
                $unitDays[$unit] = ($unitDays[$unit] ?? 0) + $days;
                $roomDays[$unit][$room] = ($roomDays[$unit][$room] ?? 0) + $days;
            }
            if ($total<=0) { $issues[] = ['code'=>'E_EMP_NO_VALID_ROOM','message'=>'No positive stay-days','severity'=>'ERROR','company_id'=>$cid]; continue; }
            foreach ($unitDays as $unit => $ud) {
                $unitAtt = ($ud/$total) * $att;
                $unitRoomTotal = array_sum($roomDays[$unit]);
                foreach ($roomDays[$unit] as $room => $rd) {
                    $alloc[] = ['company_id'=>$cid,'unit_id'=>$unit,'room_id'=>$room,'attendance_days'=>round(($rd/$unitRoomTotal)*$unitAtt,4)];
                }
            }
        }
        return ['allocations'=>$alloc,'issues'=>$issues];
    }
}
