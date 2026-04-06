<?php

namespace Tests\Support;

class ElectricV1SnapshotNormalizer
{
    private const VOLATILE_KEYS = [
        'id', 'run_id', 'logged_at', 'run_start', 'run_end', 'created_at', 'updated_at',
    ];

    public static function normalize(array $bundle): array
    {
        $norm = $bundle;
        foreach (['final_outputs','drilldown_outputs','exceptions','run_history'] as $k) {
            $rows = $norm[$k] ?? [];
            $rows = array_map([self::class, 'normalizeRow'], $rows);
            usort($rows, fn($a,$b) => strcmp(json_encode($a), json_encode($b)));
            $norm[$k] = $rows;
        }
        return $norm;
    }

    private static function normalizeRow(array $row): array
    {
        foreach (self::VOLATILE_KEYS as $vk) {
            unset($row[$vk]);
        }

        foreach ($row as $k => $v) {
            if (is_numeric($v)) {
                $row[$k] = (float) number_format((float)$v, 4, '.', '');
            }
        }
        ksort($row);
        return $row;
    }

    public static function hash(array $bundle): string
    {
        return hash('sha256', json_encode(self::normalize($bundle), JSON_UNESCAPED_SLASHES));
    }
}
