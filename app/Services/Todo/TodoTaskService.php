<?php

namespace App\Services\Todo;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class TodoTaskService
{
    private const TABLE = 'todo_tasks';

    private const STATUSES = ['open', 'in_progress', 'blocked', 'done', 'archived'];
    private const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    public function list(array $filters = []): array
    {
        if (!Schema::hasTable(self::TABLE)) {
            return [
                'schema_ready' => false,
                'rows' => [],
                'message' => 'todo_tasks table is not available. Run the Phase 5 migration in the intended environment first.',
            ];
        }

        $query = DB::table(self::TABLE);

        foreach (['status', 'priority', 'month_cycle', 'category'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $query->where($field, $value);
            }
        }

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('task_key', 'like', '%' . $search . '%');
            });
        }

        $limit = max(10, min(100, (int)($filters['limit'] ?? 50)));

        return [
            'schema_ready' => true,
            'rows' => $query
                ->orderByRaw("case status when 'blocked' then 0 when 'open' then 1 when 'in_progress' then 2 when 'done' then 3 else 4 end")
                ->orderByRaw("case priority when 'critical' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
                ->orderBy('due_date')
                ->orderByDesc('id')
                ->limit($limit)
                ->get(),
        ];
    }

    public function create(array $input, ?int $userId = null): object
    {
        $payload = $this->sanitize($input, true);
        $now = now();
        $payload['created_by_user_id'] = $userId;
        $payload['updated_by_user_id'] = $userId;
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $id = DB::table(self::TABLE)->insertGetId($payload);

        return DB::table(self::TABLE)->where('id', $id)->first();
    }

    public function update(int $id, array $input, ?int $userId = null): object
    {
        $payload = $this->sanitize($input, false);
        $payload['updated_by_user_id'] = $userId;
        $payload['updated_at'] = now();

        if (($payload['status'] ?? null) === 'done') {
            $payload['completed_at'] = $payload['completed_at'] ?? now();
        }

        DB::table(self::TABLE)->where('id', $id)->update($payload);

        return DB::table(self::TABLE)->where('id', $id)->first();
    }

    public function complete(int $id, ?int $userId = null): object
    {
        DB::table(self::TABLE)->where('id', $id)->update([
            'status' => 'done',
            'completed_at' => now(),
            'updated_by_user_id' => $userId,
            'updated_at' => now(),
        ]);

        return DB::table(self::TABLE)->where('id', $id)->first();
    }

    public function archive(int $id, ?int $userId = null): object
    {
        DB::table(self::TABLE)->where('id', $id)->update([
            'status' => 'archived',
            'archived_at' => now(),
            'updated_by_user_id' => $userId,
            'updated_at' => now(),
        ]);

        return DB::table(self::TABLE)->where('id', $id)->first();
    }

    private function sanitize(array $input, bool $creating): array
    {
        $payload = [];

        $title = trim((string)($input['title'] ?? ''));
        if ($creating && $title === '') {
            throw new InvalidArgumentException('Task title is required.');
        }
        if ($title !== '') {
            $payload['title'] = mb_substr($title, 0, 180);
        }

        foreach (['task_key' => 80, 'category' => 60, 'month_cycle' => 20] as $field => $length) {
            if (array_key_exists($field, $input)) {
                $value = trim((string)($input[$field] ?? ''));
                $payload[$field] = $value === '' ? null : mb_substr($value, 0, $length);
            }
        }

        if (array_key_exists('description', $input)) {
            $description = trim((string)($input['description'] ?? ''));
            $payload['description'] = $description === '' ? null : $description;
        }

        $status = trim((string)($input['status'] ?? ($creating ? 'open' : '')));
        if ($status !== '') {
            if (!in_array($status, self::STATUSES, true)) {
                throw new InvalidArgumentException('Invalid task status.');
            }
            $payload['status'] = $status;
        }

        $priority = trim((string)($input['priority'] ?? ($creating ? 'medium' : '')));
        if ($priority !== '') {
            if (!in_array($priority, self::PRIORITIES, true)) {
                throw new InvalidArgumentException('Invalid task priority.');
            }
            $payload['priority'] = $priority;
        }

        foreach (['assigned_to_user_id'] as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
                $payload[$field] = $value === null || $value === '' ? null : (int)$value;
            }
        }

        if (array_key_exists('due_date', $input)) {
            $dueDate = trim((string)($input['due_date'] ?? ''));
            $payload['due_date'] = $dueDate === '' ? null : $dueDate;
        }

        return $payload;
    }
}
