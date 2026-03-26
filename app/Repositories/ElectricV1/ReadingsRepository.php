<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class ReadingsRepository extends BaseRepository
{
    public function listCycleReadings(string $cycleStart, string $cycleEnd): array
    {
        return $this->all('SELECT cycle_start_date, cycle_end_date, unit_id, previous_reading, current_reading, reading_status, updated_at FROM electric_v1_readings WHERE cycle_start_date=? AND cycle_end_date=?', [$cycleStart, $cycleEnd]);
    }

    public function listUnitHistory(string $unitId, string $cycleStart): array
    {
        return $this->all('SELECT cycle_start_date, cycle_end_date, previous_reading, current_reading, reading_status FROM electric_v1_readings WHERE unit_id=? AND cycle_end_date < ? ORDER BY cycle_end_date DESC', [$unitId, $cycleStart]);
    }

    public function upsertMany(array $rows): int
    {
        foreach ($rows as $r) {
            DB::statement('INSERT INTO electric_v1_readings(cycle_start_date,cycle_end_date,unit_id,previous_reading,current_reading,reading_status,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(unit_id,cycle_start_date,cycle_end_date) DO UPDATE SET previous_reading=excluded.previous_reading, current_reading=excluded.current_reading, reading_status=excluded.reading_status, updated_at=CURRENT_TIMESTAMP', [
                $r['cycle_start_date'],$r['cycle_end_date'],$r['unit_id'],$r['previous_reading'],$r['current_reading'],$r['reading_status']
            ]);
        }
        return count($rows);
    }
}
