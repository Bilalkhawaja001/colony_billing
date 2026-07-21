<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const VALID_TYPES = [
        'BACHELOR',
        'CONTAINER',
        'HOSTEL',
        'HOUSE',
    ];

    private const REVIEWED_UNIT_OVERRIDES = [
        // NBC is a bachelor colony at unit level; two room labels contain Hostel.
        'NBC-1-100' => 'BACHELOR',
        // WE-001 is room-mode (WE-001-1/2/3), not a full-allowance HOUSE unit.
        'WE-001' => 'BACHELOR',
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('electric_v1_allowance', 'allowance_type')) {
            Schema::table('electric_v1_allowance', function (Blueprint $table) {
                $table->string('allowance_type', 16)
                    ->nullable()
                    ->after('residence_type')
                    ->index('ev1_allowance_type_idx');
            });
        }

        $sourceMaps = $this->sourceMaps();

        DB::table('electric_v1_allowance')
            ->select(['id', 'unit_id', 'room_no', 'residence_type', 'allowance_type'])
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($sourceMaps) {
                $existingType = $this->classify((string) ($row->allowance_type ?? ''));

                if ($existingType !== null) {
                    return;
                }

                $billingType = strtoupper(trim((string) $row->residence_type));

                $unit = $this->keyPart((string) $row->unit_id);

                if ($billingType === 'HOUSE') {
                    $classification = 'HOUSE';
                } elseif (array_key_exists($unit, self::REVIEWED_UNIT_OVERRIDES)) {
                    $classification = self::REVIEWED_UNIT_OVERRIDES[$unit];
                } else {
                    $room = $this->keyPart((string) ($row->room_no ?? ''));
                    $classification = $this->uniqueType($sourceMaps['exact'][$unit.'|'.$room] ?? []);

                    if ($classification === null) {
                        $classification = $this->uniqueType($sourceMaps['unit'][$unit] ?? []);
                    }
                }

                if ($classification !== null) {
                    DB::table('electric_v1_allowance')
                        ->where('id', $row->id)
                        ->update([
                            'allowance_type' => $classification,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('electric_v1_allowance', 'allowance_type')) {
            Schema::table('electric_v1_allowance', function (Blueprint $table) {
                $table->dropIndex('ev1_allowance_type_idx');
                $table->dropColumn('allowance_type');
            });
        }
    }

    private function sourceMaps(): array
    {
        $maps = [
            'exact' => [],
            'unit' => [],
        ];

        foreach (['util_unit_room_snapshot', 'employee_residence_assignments'] as $table) {
            if (
                !Schema::hasTable($table) ||
                !Schema::hasColumn($table, 'unit_id') ||
                !Schema::hasColumn($table, 'residence_type')
            ) {
                continue;
            }

            $roomColumn = Schema::hasColumn($table, 'room_no')
                ? 'room_no'
                : (Schema::hasColumn($table, 'room_id') ? 'room_id' : null);

            $columns = ['unit_id', 'residence_type'];

            if ($roomColumn !== null) {
                $columns[] = $roomColumn;
            }

            $tableMaps = [
                'exact' => [],
                'unit' => [],
            ];

            foreach (DB::table($table)->select($columns)->get() as $source) {
                $classification = $this->classify((string) $source->residence_type);

                if ($classification === null) {
                    continue;
                }

                $unit = $this->keyPart((string) $source->unit_id);
                $room = $roomColumn === null
                    ? ''
                    : $this->keyPart((string) ($source->{$roomColumn} ?? ''));

                if ($unit === '') {
                    continue;
                }

                $tableMaps['unit'][$unit][] = $classification;
                $tableMaps['exact'][$unit.'|'.$room][] = $classification;
            }

            foreach (['exact', 'unit'] as $mapType) {
                foreach ($tableMaps[$mapType] as $key => $types) {
                    if (!array_key_exists($key, $maps[$mapType])) {
                        $maps[$mapType][$key] = $types;
                    }
                }
            }
        }

        return $maps;
    }

    private function classify(string $value): ?string
    {
        $value = strtoupper(trim($value));

        if ($value === '') {
            return null;
        }

        if (str_contains($value, 'HOUSE') || str_contains($value, 'FAMILY COLONY')) {
            return 'HOUSE';
        }

        if (str_contains($value, 'HOSTEL') || str_contains($value, 'GUEST HOUSE')) {
            return 'HOSTEL';
        }

        if (str_contains($value, 'CONTAINER')) {
            return 'CONTAINER';
        }

        if (str_contains($value, 'BACHELOR')) {
            return 'BACHELOR';
        }

        return in_array($value, self::VALID_TYPES, true) ? $value : null;
    }

    private function uniqueType(array $types): ?string
    {
        $types = array_values(array_unique(array_filter($types)));

        return count($types) === 1 ? $types[0] : null;
    }

    private function keyPart(string $value): string
    {
        return strtoupper(trim($value));
    }
};
