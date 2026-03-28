<?php

namespace App\Services\ElectricV1\Domain;

class AdjustmentsEngine
{
    public static function compute(float $grossUnits, float $freeUnits, float $adjustmentUnits, float $flatRate): array
    {
        $netBefore = $grossUnits - $freeUnits;
        $netAfter = $netBefore + $adjustmentUnits;
        if ($netAfter < 0) $netAfter = 0;
        $amountBefore = $netAfter * $flatRate;
        $rounded = Rules::customFinalRound($amountBefore);

        return [
            'gross_units' => $grossUnits,
            'free_allowance_units' => $freeUnits,
            'net_units_before_adj' => $netBefore,
            'adjustment_units' => $adjustmentUnits,
            'net_units_after_adj' => $netAfter,
            'amount_before_rounding' => $amountBefore,
            'final_amount_rounded' => $rounded,
        ];
    }
}
