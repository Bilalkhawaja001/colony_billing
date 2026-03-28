<?php

namespace App\Services\ElectricV1\Domain;

class Rules
{
    public static function customFinalRound(float $amount): float
    {
        $floor = floor($amount);
        $frac = $amount - $floor;
        return $frac <= 0.50 ? (float)$floor : (float)($floor + 1);
    }
}
