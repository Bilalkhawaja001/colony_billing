<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1ApiRoleMatrixTest extends TestCase
{
    public function test_data_entry_can_run_and_upsert(): void
    {
        $this->withSession(['user_id'=>3,'role'=>'DATA_ENTRY','force_change_password'=>0]);
        $this->postJson('/api/electric-v1/input/allowance/upsert', ['rows'=>[["unit_id"=>"UM","free_electric"=>1,"residence_type"=>"ROOM"]]])->assertStatus(200);
        $this->postJson('/api/electric-v1/run', ['cycle_start'=>'2026-03-01','cycle_end'=>'2026-03-31','flat_rate'=>1])->assertStatus(200);
    }
}
