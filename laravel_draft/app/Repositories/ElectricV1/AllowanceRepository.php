<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class AllowanceRepository extends BaseRepository
{
    public function listAllowances(): array
    {
        return $this->all(
            'SELECT unit_id, free_electric, unit_name, residence_type, updated_at
             FROM electric_v1_allowance
             WHERE is_active = 1'
        );
    }

    public function upsertMany(array $rows): int
    {
        return DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $unitId = strtoupper(trim((string) $row['unit_id']));

                $matches = DB::table('electric_v1_allowance')
                    ->where('unit_id', $unitId)
                    ->lockForUpdate()
                    ->pluck('id');

                if ($matches->count() > 1) {
                    throw new \RuntimeException(
                        "Duplicate allowance records already exist for Unit ID {$unitId}."
                    );
                }

                $payload = [
                    'unit_id' => $unitId,
                    'free_electric' => $row['free_electric'],
                    'unit_name' => $row['unit_name'] ?? null,
                    'residence_type' => strtoupper(trim((string) $row['residence_type'])),
                    'updated_at' => now(),
                ];

                if ($matches->count() === 1) {
                    DB::table('electric_v1_allowance')
                        ->where('id', $matches->first())
                        ->update($payload);
                } else {
                    $payload['is_active'] = true;
                    $payload['created_at'] = now();

                    DB::table('electric_v1_allowance')->insert($payload);
                }
            }

            return count($rows);
        });
    }
}
