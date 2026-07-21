<?php
namespace App\Services\BillingEngine;

use App\Services\BillingEngine\Contracts\BillingMethod;
use App\Services\BillingEngine\Methods\AttendanceProrated;
use App\Services\BillingEngine\Methods\OccupiedRoomEqualSplit;

class MethodRegistry
{
    /** @return BillingMethod[] code => instance */
    public static function all(): array
    {
        $methods = [
            new AttendanceProrated(),
            new OccupiedRoomEqualSplit(),
        ];
        $out = [];
        foreach ($methods as $m) { $out[$m->code()] = $m; }
        return $out;
    }

    public static function get(string $code): ?BillingMethod
    {
        return self::all()[$code] ?? null;
    }

    /** dropdown ke liye: code => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::all() as $code => $m) { $out[$code] = $m->label(); }
        return $out;
    }
}
