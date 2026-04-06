<?php

namespace App\Services\ElectricV1\Domain;

class ExceptionCatalog
{
    public const HARD_STOPS = [
        'E_CTRL_DATE_INVALID',
        'E_CTRL_RATE_INVALID',
        'E_ALLOW_DUP_KEY',
        'E_READ_DUP_KEY',
        'E_READ_REVERSE',
        'E_HOUSE_RESP_NOT_SINGLE',
        'E_UNIT_ZERO_ATT_WITH_CONS',
    ];

    public static function isHardStop(string $code): bool
    {
        return in_array($code, self::HARD_STOPS, true);
    }
}
