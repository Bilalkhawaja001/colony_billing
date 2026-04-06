<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    protected function all(string $sql, array $params = []): array
    {
        return array_map(fn($r) => (array)$r, DB::select($sql, $params));
    }

    protected function one(string $sql, array $params = []): ?array
    {
        $r = DB::selectOne($sql, $params);
        return $r ? (array)$r : null;
    }
}
