<?php

namespace App\Services\Billing\V2;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AuditLogService
{
    public function append(array $event): int
    {
        $action = trim((string) ($event['action'] ?? ''));
        $entityType = trim((string) ($event['entity_type'] ?? ''));

        if ($action === '') {
            throw new \InvalidArgumentException('audit action is required');
        }

        if ($entityType === '') {
            throw new \InvalidArgumentException('audit entity_type is required');
        }

        $payload = [
            'run_id' => $event['run_id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $event['entity_id'] ?? null,
            'actor_user_id' => $event['actor_user_id'] ?? null,
            'actor_username' => $event['actor_username'] ?? null,
            'before_json' => $this->jsonOrNull($event['before'] ?? $event['before_json'] ?? null),
            'after_json' => $this->jsonOrNull($event['after'] ?? $event['after_json'] ?? null),
            'meta_json' => $this->jsonOrNull($event['meta'] ?? $event['meta_json'] ?? null),
            'source_file_name' => $event['source_file_name'] ?? null,
            'upload_hash' => $event['upload_hash'] ?? null,
            'correlation_id' => $event['correlation_id'] ?? (string) Str::uuid(),
            'ip_address' => $event['ip_address'] ?? request()?->ip(),
            'session_id' => $event['session_id'] ?? session()?->getId(),
            'created_at' => now(),
        ];

        return (int) DB::table('audit_log')->insertGetId($payload);
    }

    public function appendRunEvent(int $runId, string $action, array $after = [], array $meta = []): int
    {
        return $this->append([
            'run_id' => $runId,
            'action' => $action,
            'entity_type' => 'bill_run',
            'entity_id' => (string) $runId,
            'actor_user_id' => session('actor_user_id') ?? session('user_id'),
            'after' => $after,
            'meta' => $meta,
        ]);
    }

    private function jsonOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $value;
            }
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
