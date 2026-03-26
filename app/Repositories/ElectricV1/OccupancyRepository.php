<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class OccupancyRepository extends BaseRepository
{
    public function listOccupancy(): array
    {
        return $this->all('SELECT company_id, unit_id, room_id, from_date, to_date, updated_at FROM electric_v1_occupancy');
    }

    public function upsertMany(array $rows): int
    {
        foreach ($rows as $r) {
            DB::statement('INSERT INTO electric_v1_occupancy(company_id,unit_id,room_id,from_date,to_date,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(company_id,unit_id,room_id,from_date,to_date) DO UPDATE SET updated_at=CURRENT_TIMESTAMP', [
                $r['company_id'],$r['unit_id'],$r['room_id'],$r['from_date'],$r['to_date']
            ]);
        }
        return count($rows);
    }
}
