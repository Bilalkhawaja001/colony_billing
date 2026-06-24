<?php

namespace App\Services\Billing\V2;

use App\Models\BillRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

final class BillRunGateService
{
    private const ROLE_MATRIX = [
        'view' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        'mark_preview_ready' => ['SUPER_ADMIN', 'BILLING_ADMIN'],
        'submit' => ['SUPER_ADMIN', 'BILLING_ADMIN'],
        'approve' => ['SUPER_ADMIN'],
        'return_to_draft' => ['SUPER_ADMIN', 'BILLING_ADMIN'],
        'mark_generated' => ['SUPER_ADMIN', 'BILLING_ADMIN'],
        'publish' => ['SUPER_ADMIN'],
        'close' => ['SUPER_ADMIN'],
        'void' => ['SUPER_ADMIN'],
    ];

    private const ACTION_TARGETS = [
        'mark_preview_ready' => BillRunStateMachine::PREVIEW_READY,
        'submit' => BillRunStateMachine::PENDING_APPROVAL,
        'approve' => BillRunStateMachine::APPROVED,
        'return_to_draft' => BillRunStateMachine::DRAFT,
        'mark_generated' => BillRunStateMachine::GENERATED,
        'publish' => BillRunStateMachine::PUBLISHED,
        'close' => BillRunStateMachine::CLOSED,
        'void' => BillRunStateMachine::VOIDED,
    ];

    public function allowedActions(BillRun $run, ?string $role): array
    {
        $role = $this->normalizeRole($role);
        $actions = [];

        foreach (self::ACTION_TARGETS as $action => $targetStatus) {
            $roleAllowed = $this->canRole($action, $role);
            $stateAllowed = BillRunStateMachine::canTransition($run->status, $targetStatus);

            $actions[$action] = [
                'allowed' => $roleAllowed && $stateAllowed,
                'target_status' => $targetStatus,
                'role_allowed' => $roleAllowed,
                'state_allowed' => $stateAllowed,
            ];
        }

        return $actions;
    }

    public function transition(int $runId, string $action, ?string $role, ?int $actorUserId = null, ?string $reason = null): array
    {
        $action = strtolower(trim($action));
        $role = $this->normalizeRole($role);

        if (!isset(self::ACTION_TARGETS[$action])) {
            throw new InvalidArgumentException('Invalid gate action: '.$action);
        }

        if (!$this->canRole($action, $role)) {
            throw new RuntimeException('Role '.$role.' is not allowed to perform '.$action);
        }

        return DB::transaction(function () use ($runId, $action, $role, $actorUserId, $reason): array {
            $run = BillRun::query()->whereKey($runId)->lockForUpdate()->firstOrFail();
            $targetStatus = self::ACTION_TARGETS[$action];

            BillRunStateMachine::assertTransition($run->status, $targetStatus);
            $this->assertPreflightGate($run, $action);

            $before = [
                'status' => $run->status,
                'committed_scope_key' => $run->committed_scope_key,
            ];

            $run->status = $targetStatus;
            $run->status_changed_at = now();

            $this->stampActor($run, $targetStatus, $actorUserId, $reason);
            $run->committed_scope_key = $this->committedScopeKeyFor($run, $targetStatus);
            $this->assertNoDuplicateCommittedScope($run);
            $run->save();

            $after = [
                'status' => $run->status,
                'committed_scope_key' => $run->committed_scope_key,
                'action' => $action,
                'actor_role' => $role,
            ];

            $this->audit($run, $action, $actorUserId, $role, $before, $after, $reason);

            return [
                'status' => 'ok',
                'bill_run_id' => $run->id,
                'action' => $action,
                'from' => $before['status'],
                'to' => $run->status,
                'committed_scope_key' => $run->committed_scope_key,
            ];
        });
    }

    public function canRole(string $action, ?string $role): bool
    {
        return in_array($this->normalizeRole($role), self::ROLE_MATRIX[$action] ?? [], true);
    }

    private function assertPreflightGate(BillRun $run, string $action): void
    {
        if (!Schema::hasTable('bill_run_preflight_checks')) {
            return;
        }

        $rows = DB::table('bill_run_preflight_checks')->where('bill_run_id', $run->id);
        $stopCount = (clone $rows)->where('severity', 'stop')->where('status', '!=', 'pass')->count();

        if ($stopCount > 0) {
            throw new RuntimeException('Bill run has stopping preflight blockers: '.$stopCount);
        }

        if ($action === 'mark_generated' && (clone $rows)->count() === 0) {
            throw new RuntimeException('Saved preflight checks are required before generate.');
        }
    }

    private function stampActor(BillRun $run, string $targetStatus, ?int $actorUserId, ?string $reason): void
    {
        if ($targetStatus === BillRunStateMachine::PENDING_APPROVAL) {
            $run->submitted_by_user_id = $actorUserId;
            $run->submitted_at = now();
        } elseif ($targetStatus === BillRunStateMachine::APPROVED) {
            $run->approved_by_user_id = $actorUserId;
            $run->approved_at = now();
        } elseif ($targetStatus === BillRunStateMachine::GENERATED) {
            $run->generated_by_user_id = $actorUserId;
            $run->generated_at = now();
        } elseif ($targetStatus === BillRunStateMachine::PUBLISHED) {
            $run->published_by_user_id = $actorUserId;
            $run->published_at = now();
        } elseif ($targetStatus === BillRunStateMachine::CLOSED) {
            $run->closed_by_user_id = $actorUserId;
            $run->closed_at = now();
        } elseif ($targetStatus === BillRunStateMachine::VOIDED) {
            $run->voided_by_user_id = $actorUserId;
            $run->voided_at = now();
            $run->void_reason = $reason;
        }
    }

    private function committedScopeKeyFor(BillRun $run, string $targetStatus): ?string
    {
        $periodKey = BillRunStateMachine::periodKey(
            $run->month_cycle,
            $this->dateString($run->cycle_start_date),
            $this->dateString($run->cycle_end_date)
        );

        return BillRunStateMachine::committedScopeKey($periodKey, (string) $run->bill_type, (string) $run->scope_hash, $targetStatus);
    }

    private function assertNoDuplicateCommittedScope(BillRun $run): void
    {
        if (!$run->committed_scope_key) {
            return;
        }

        $exists = BillRun::query()->where('committed_scope_key', $run->committed_scope_key)->whereKeyNot($run->id)->exists();
        if ($exists) {
            throw new RuntimeException('Duplicate committed bill run scope is blocked.');
        }
    }

    private function audit(BillRun $run, string $action, ?int $actorUserId, string $role, array $before, array $after, ?string $reason): void
    {
        if (!Schema::hasTable('audit_log')) {
            return;
        }

        app(AuditLogService::class)->append([
            'run_id' => $run->id,
            'action' => 'BILL_RUN_GATE_'.$action,
            'entity_type' => 'bill_run',
            'entity_id' => (string) $run->id,
            'actor_user_id' => $actorUserId,
            'actor_username' => $role,
            'before' => $before,
            'after' => $after,
            'meta' => ['reason' => $reason],
        ]);
    }

    private function normalizeRole(?string $role): string
    {
        return strtoupper(trim((string) ($role ?: 'VIEWER')));
    }

    private function dateString(mixed $date): string
    {
        return $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : substr((string) $date, 0, 10);
    }
}
