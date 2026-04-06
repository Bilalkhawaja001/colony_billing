<?php

namespace App\Services\ElectricV1\Domain;

class Validators
{
    public static function uniqueByKey(array $rows, callable $keyFn, string $code, string $message): array
    {
        $seen = [];
        $issues = [];
        foreach ($rows as $r) {
            $k = $keyFn($r);
            if ($k === '') continue;
            if (isset($seen[$k])) {
                $issues[] = ['code' => $code, 'message' => $message.': '.$k, 'severity' => 'ERROR'];
                break;
            }
            $seen[$k] = true;
        }
        return $issues;
    }
}
