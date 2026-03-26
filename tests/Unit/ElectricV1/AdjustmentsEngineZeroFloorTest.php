<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\AdjustmentsEngine;
use Tests\TestCase;

class AdjustmentsEngineZeroFloorTest extends TestCase
{
    public function test_adjusted_net_floor_to_zero(): void
    {
        $r = AdjustmentsEngine::compute(5, 10, -1, 2.0);
        $this->assertSame(0.0, $r['net_units_after_adj']);
        $this->assertSame(0.0, $r['amount_before_rounding']);
    }
}
