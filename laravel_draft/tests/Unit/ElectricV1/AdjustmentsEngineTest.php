<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\AdjustmentsEngine;
use Tests\TestCase;

class AdjustmentsEngineTest extends TestCase
{
    public function test_post_net_adjustment_and_custom_rounding(): void
    {
        $r = AdjustmentsEngine::compute(100, 20, -10, 2.3);
        $this->assertEquals(70.0, $r['net_units_after_adj']);
        $this->assertEquals(161.0, $r['final_amount_rounded']);
    }
}
