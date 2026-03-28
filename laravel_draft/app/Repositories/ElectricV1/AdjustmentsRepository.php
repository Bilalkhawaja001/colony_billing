<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class AdjustmentsRepository extends BaseRepository
{
    public function listCycleAdjustments(string $cycleStart, string $cycleEnd): array
    {
        return $this->all('SELECT cycle_start_date, cycle_end_date, company_id, unit_id, adjustment_units, updated_at FROM electric_v1_adjustments WHERE cycle_start_date=? AND cycle_end_date=?', [$cycleStart, $cycleEnd]);
    }

    public function upsertMany(array $rows): int
    {
        foreach ($rows as $r) {
            DB::statement('INSERT INTO electric_v1_adjustments(cycle_start_date,cycle_end_date,company_id,unit_id,adjustment_units,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(company_id,unit_id,cycle_start_date,cycle_end_date) DO UPDATE SET adjustment_units=excluded.adjustment_units, updated_at=CURRENT_TIMESTAMP', [
                $r['cycle_start_date'],$r['cycle_end_date'],$r['company_id'],$r['unit_id'],$r['adjustment_units']
            ]);
        }
        return count($rows);
    }
}
