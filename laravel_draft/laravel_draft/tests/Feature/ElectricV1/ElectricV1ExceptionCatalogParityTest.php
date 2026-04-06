<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1ExceptionCatalogParityTest extends TestCase
{
    public function test_reverse_read_emits_expected_exception_code(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);

        $this->postJson('/api/electric-v1/input/allowance/upsert', ['rows'=>[["unit_id"=>"U-EX","free_electric"=>0,"unit_name"=>"U-EX","residence_type"=>"ROOM"]]])->assertOk();
        $this->postJson('/api/electric-v1/input/readings/upsert', ['rows'=>[["cycle_start_date"=>"2026-06-01","cycle_end_date"=>"2026-06-30","unit_id"=>"U-EX","previous_reading"=>200,"current_reading"=>100,"reading_status"=>"NORMAL"]]])->assertOk();
        $this->postJson('/api/electric-v1/run', ['cycle_start'=>'2026-06-01','cycle_end'=>'2026-06-30','flat_rate'=>2])->assertOk();

        $exc = $this->getJson('/api/electric-v1/exceptions?cycle_start=2026-06-01&cycle_end=2026-06-30')->json('data');
        $codes = array_map(fn($x)=>$x['exception_code'] ?? null, $exc ?: []);
        $this->assertContains('E_READ_REVERSE', $codes);
    }
}
