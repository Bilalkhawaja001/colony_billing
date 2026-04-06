<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase5UiShellVsWorkflowTruthTest extends TestCase
{
    public function test_ui_billing_is_shell_page_not_workflow_completion_proof(): void
    {
        $this->withSession([
            'user_id' => 7101,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        $this->get('/ui/billing')
            ->assertOk()
            ->assertSee('Parity draft page is active');
    }

    public function test_workflow_endpoint_has_independent_contract_from_shell_render(): void
    {
        $this->withSession([
            'user_id' => 7102,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        // same module family, but endpoint enforces month lifecycle contract
        $this->postJson('/billing/run', ['month_cycle' => '03-2026', 'run_key' => 'UI-SHELL-TRUTH'])
            ->assertStatus(409)
            ->assertJsonPath('status', 'error');
    }
}
