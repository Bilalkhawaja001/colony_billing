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
            DB::insert('INSERT INTO electric_v1_allowance(unit_id,free_electric,unit_name,residence_type,updated_at,created_at) VALUES(?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)', [
                $r['unit_id'], $r['free_electric'], $r['unit_name'] ?? null, $r['residence_type']
            ]);
        }
        return count($rows);
    }
}
