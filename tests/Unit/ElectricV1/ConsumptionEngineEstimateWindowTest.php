<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\ConsumptionEngine;
use Tests\TestCase;

class ConsumptionEngineEstimateWindowTest extends TestCase
{
    public function test_estimate_uses_up_to_three_valid_cycles(): void
    {
        $history = [
            ['reading_status'=>'NORMAL','previous_reading'=>0,'current_reading'=>10,'cycle_start_date'=>'2026-01-01','cycle_end_date'=>'2026-01-31'],
            ['reading_status'=>'FAULTY','previous_reading'=>0,'current_reading'=>99,'cycle_start_date'=>'2026-02-01','cycle_end_date'=>'2026-02-28'],
            ['reading_status'=>'NORMAL','previous_reading'=>10,'current_reading'=>30,'cycle_start_date'=>'2026-03-01','cycle_end_date'=>'2026-03-31'],
            ['reading_status'=>'NORMAL','previous_reading'=>30,'current_reading'=>45,'cycle_start_date'=>'2026-04-01','cycle_end_date'=>'2026-04-30'],
        ];
        $res = ConsumptionEngine::compute('U1', ['reading_status'=>'FAULTY'], $history);
        $this->assertNotNull($res['result']);
        $this->assertEquals(3, $res['result']['estimated_from_valid_cycle_count']);
    }
}
