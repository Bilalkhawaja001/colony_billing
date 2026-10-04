<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class AllowanceRepository extends BaseRepository
{
    public function listAllowances(): array
    {
        /*
         * util_unit_rooms is authoritative for physical residence type.
         *
         * HOUSE_A / HOUSE_A+ / HOUSE_B / HOUSE_C etc. are normalised
         * to billing type HOUSE.
         *
         * For non-house units, retain electric_v1_allowance residence_type.
         */

        $houseUnits = [];

        foreach (
            DB::table('util_unit_rooms')
                ->whereRaw("UPPER(TRIM(COALESCE(residence_type, ''))) LIKE 'HOUSE%'")
                ->pluck('unit_id') as $unitId
        ) {
            $unitId = strtoupper(trim((string) $unitId));

            if ($unitId !== '') {
                $houseUnits[$unitId] = true;
            }
        }

        /*
         * There may be legacy duplicate active allowance rows.
         * Read newest active row only for each unit.
         */
        $rows = DB::table('electric_v1_allowance')
            ->where('is_active', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $byUnit = [];

        foreach ($rows as $row) {
            $unitId = strtoupper(trim((string) $row->unit_id));

            if ($unitId === '' || isset($byUnit[$unitId])) {
                continue;
            }

            $byUnit[$unitId] = [
                'unit_id' => $unitId,
                'free_electric' => $row->free_electric,
                'unit_name' => $row->unit_name,
                'residence_type' => isset($houseUnits[$unitId])
                    ? 'HOUSE'
                    : strtoupper(trim((string) $row->residence_type)),
                'updated_at' => $row->updated_at,
            ];
        }

        ksort($byUnit);

        return array_values($byUnit);
    }

    public function upsertMany(array $rows): int
    {
        return DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $unitId = strtoupper(trim((string) $row['unit_id']));

                /*
                 * Only active rows participate in current configuration.
                 * If legacy duplicates exist, keep newest and deactivate rest.
                 */
                $matches = DB::table('electric_v1_allowance')
                    ->where('unit_id', $unitId)
                    ->where('is_active', 1)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->pluck('id');

                $keepId = $matches->first();

                if ($matches->count() > 1) {
                    $duplicateIds = $matches
                        ->slice(1)
                        ->values()
                        ->all();

                    DB::table('electric_v1_allowance')
                        ->whereIn('id', $duplicateIds)
                        ->update([
                            'is_active' => false,
                            'updated_at' => now(),
                        ]);
                }

                /*
                 * Physical master decides whether a unit is HOUSE.
                 */
                $isHouse = DB::table('util_unit_rooms')
                    ->where('unit_id', $unitId)
                    ->whereRaw("UPPER(TRIM(COALESCE(residence_type, ''))) LIKE 'HOUSE%'")
                    ->exists();

                $residenceType = $isHouse
                    ? 'HOUSE'
                    : strtoupper(trim((string) ($row['residence_type'] ?? 'ROOM')));

                $payload = [
                    'unit_id' => $unitId,
                    'free_electric' => $row['free_electric'],
                    'unit_name' => $row['unit_name'] ?? null,
                    'residence_type' => $residenceType,
                    'updated_at' => now(),
                ];

                if ($keepId) {
                    DB::table('electric_v1_allowance')
                        ->where('id', $keepId)
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
