<?php

namespace App\Services\Billing\V2;

use App\Models\BillRun;
use Illuminate\Support\Facades\Schema;

final class BillRunSnapshotManifestService
{
    public const SOURCES = [
        'attendance' => 'electric_v1_hr_attendance',
        'readings' => 'electric_v1_readings',
        'occupancy' => 'electric_v1_occupancy',
        'room_allowance' => 'electric_v1_room_allowance',
        'unit_allowance' => 'electric_v1_allowance',
        'rates' => 'util_monthly_rates_config',
    ];

    public function manifestFor(BillRun $run): array
    {
        $sources = [];

        foreach (self::SOURCES as $type => $table) {
            $sources[$type] = [
                'table' => $table,
                'exists' => Schema::hasTable($table),
            ];
        }

        $manifest = [
            'bill_run_id' => $run->id,
            'month_cycle' => $run->month_cycle,
            'cycle_start_date' => $this->dateString($run->cycle_start_date),
            'cycle_end_date' => $this->dateString($run->cycle_end_date),
            'sources' => $sources,
        ];

        $manifest['manifest_hash'] = hash('sha256', json_encode($manifest, JSON_UNESCAPED_SLASHES));

        return $manifest;
    }

    public function rowHash(array $row): string
    {
        ksort($row);

        return hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function dateString(mixed $date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return substr((string) $date, 0, 10);
    }
}
