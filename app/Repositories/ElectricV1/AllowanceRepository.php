<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class AllowanceRepository extends BaseRepository
{
    public function listAllowances(): array
    {
        return $this->all('SELECT unit_id, free_electric, unit_name, residence_type, updated_at FROM electric_v1_allowance');
    }

    public function upsertMany(array $rows): int
    {
        foreach ($rows as $r) {
            DB::statement('INSERT INTO electric_v1_allowance(unit_id,free_electric,unit_name,residence_type,updated_at) VALUES(?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(unit_id) DO UPDATE SET free_electric=excluded.free_electric, unit_name=excluded.unit_name, residence_type=excluded.residence_type, updated_at=CURRENT_TIMESTAMP', [
                $r['unit_id'], $r['free_electric'], $r['unit_name'] ?? null, $r['residence_type']
            ]);
        }
        return count($rows);
    }
}
