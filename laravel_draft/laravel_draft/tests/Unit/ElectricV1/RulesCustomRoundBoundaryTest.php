<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\Rules;
use Tests\TestCase;

class RulesCustomRoundBoundaryTest extends TestCase
{
    public function test_rounding_boundaries(): void
    {
        $this->assertSame(10.0, Rules::customFinalRound(10.50));
        $this->assertSame(11.0, Rules::customFinalRound(10.51));
        $this->assertSame(10.0, Rules::customFinalRound(10.49));
    }
}
