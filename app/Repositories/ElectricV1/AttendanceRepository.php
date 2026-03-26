<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class AttendanceRepository extends BaseRepository
{
    public function listCycleAttendance(string $cycleStart, string $cycleEnd): array
    {
        return $this->all('SELECT cycle_start_date, cycle_end_date, company_id, attendance_days, updated_at FROM electric_v1_hr_attendance WHERE cycle_start_date=? AND cycle_end_date=?', [$cycleStart, $cycleEnd]);
    }

    public function upsertMany(array $rows): int
    {
        foreach ($rows as $r) {
            DB::statement('INSERT INTO electric_v1_hr_attendance(cycle_start_date,cycle_end_date,company_id,attendance_days,updated_at) VALUES(?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(company_id,cycle_start_date,cycle_end_date) DO UPDATE SET attendance_days=excluded.attendance_days, updated_at=CURRENT_TIMESTAMP', [
                $r['cycle_start_date'],$r['cycle_end_date'],$r['company_id'],$r['attendance_days']
            ]);
        }
        return count($rows);
    }
}
