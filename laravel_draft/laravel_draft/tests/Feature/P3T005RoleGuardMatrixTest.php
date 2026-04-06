<?php

namespace Tests\Feature;

use Tests\TestCase;

class P3T005RoleGuardMatrixTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9701,
            'actor_user_id' => 9701,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function asViewer(): void
    {
        $this->withSession([
            'user_id' => 9702,
            'actor_user_id' => 9702,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public function test_p3_t005_role_and_guard_matrix_across_p3_chains(): void
    {
        $this->asAdmin();
        $month = '01-2027';

        // Base month lifecycle
        $this->postJson('/month/open', ['month_cycle' => $month])->assertOk()->assertJsonPath('state', 'OPEN');

        // Invalid transition + malformed payload failures
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'INVALID_STATE'])->assertStatus(422);
        $this->postJson('/month/transition', ['month_cycle' => 'bad-month', 'to_state' => 'INGEST'])->assertStatus(422);

        // Valid transition chain to APPROVAL
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'INGEST'])->assertOk();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'VALIDATION'])->assertOk();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'APPROVAL'])->assertOk();

        // Malformed billing payload
        $this->postJson('/billing/run', ['month_cycle' => 'bad'])->assertStatus(422);

        // Viewer denied across write endpoints used in P3 chains
        $this->asViewer();

        $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'VIEWER-DENY'])
            ->assertStatus(403);

        $this->postJson('/billing/elec/compute', ['month_cycle' => $month])
            ->assertStatus(403);

        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => $month,
            'rows' => [['water_zone' => 'FAMILY_METER', 'raw_liters' => 1000, 'common_use_liters' => 100]],
        ])->assertStatus(403);

        $this->postJson('/family/details/upsert', [
            'month_cycle' => $month,
            'company_id' => 'E5001',
            'family_member_name' => 'Child',
            'relation' => 'Son',
            'age' => 8,
        ])->assertStatus(403);

        // Locked-month write blocks for admin on core write path
        $this->asAdmin();
        $this->postJson('/month/transition', ['month_cycle' => $month, 'to_state' => 'LOCKED'])->assertOk()->assertJsonPath('state', 'LOCKED');

        $this->postJson('/billing/run', ['month_cycle' => $month, 'run_key' => 'LOCKED-DENY'])
            ->assertStatus(409);

        $this->postJson('/monthly-rates/config/upsert', [
            'month_cycle' => $month,
            'elec_rate' => 10,
            'water_general_rate' => 2,
            'water_drinking_rate' => 3,
            'school_van_rate' => 100,
        ])->assertStatus(409)->assertJsonPath('guard', 'month.guard.domain');
    }
}
