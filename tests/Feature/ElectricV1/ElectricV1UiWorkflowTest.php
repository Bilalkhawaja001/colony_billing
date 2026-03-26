<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1UiWorkflowTest extends TestCase
{
    public function test_pages_render_and_contain_expected_markers(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);
        $this->get('/ui/electric-v1-run')->assertOk()->assertSee('ElectricBillingV1 Run');
        $this->get('/ui/electric-v1-outputs')->assertOk()->assertSee('ElectricBillingV1 Outputs');
    }
}
