<?php

namespace App\Models;

use App\Services\Billing\V2\BillRunStateMachine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BillRun extends Model
{
    public const STATUS_DRAFT = BillRunStateMachine::DRAFT;
    public const STATUS_PREVIEW_READY = BillRunStateMachine::PREVIEW_READY;
    public const STATUS_PENDING_APPROVAL = BillRunStateMachine::PENDING_APPROVAL;
    public const STATUS_APPROVED = BillRunStateMachine::APPROVED;
    public const STATUS_GENERATED = BillRunStateMachine::GENERATED;
    public const STATUS_PUBLISHED = BillRunStateMachine::PUBLISHED;
    public const STATUS_CLOSED = BillRunStateMachine::CLOSED;
    public const STATUS_VOIDED = BillRunStateMachine::VOIDED;

    protected $table = 'bill_runs';

    protected $fillable = [
        'run_uuid',
        'source',
        'bill_type',
        'month_cycle',
        'cycle_start_date',
        'cycle_end_date',
        'cycle_days',
        'scope_type',
        'scope_hash',
        'scope_payload_json',
        'committed_scope_key',
        'status',
        'approval_required',
        'corrects_run_id',
        'idempotency_key',
        'snapshot_version',
        'readiness_result_json',
        'summary_json',
        'created_by_user_id',
        'submitted_by_user_id',
        'submitted_at',
        'approved_by_user_id',
        'approved_at',
        'generated_by_user_id',
        'generated_at',
        'published_by_user_id',
        'published_at',
        'closed_by_user_id',
        'closed_at',
        'voided_by_user_id',
        'voided_at',
        'void_reason',
        'status_changed_at',
    ];

    protected $casts = [
        'cycle_start_date' => 'date',
        'cycle_end_date' => 'date',
        'cycle_days' => 'integer',
        'scope_payload_json' => 'array',
        'approval_required' => 'boolean',
        'snapshot_version' => 'integer',
        'readiness_result_json' => 'array',
        'summary_json' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'generated_at' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'voided_at' => 'datetime',
        'status_changed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BillRun $run): void {
            if (empty($run->run_uuid)) {
                $run->run_uuid = (string) Str::uuid();
            }

            $run->status = BillRunStateMachine::normalize($run->status ?: BillRunStateMachine::DRAFT);

            if (!$run->cycle_days && $run->cycle_start_date && $run->cycle_end_date) {
                $run->cycle_days = BillRunStateMachine::cycleDays(
                    (string) $run->cycle_start_date,
                    (string) $run->cycle_end_date
                );
            }
        });
    }

    public function corrects()
    {
        return $this->belongsTo(self::class, 'corrects_run_id');
    }

    public function corrections()
    {
        return $this->hasMany(self::class, 'corrects_run_id');
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        return BillRunStateMachine::canTransition($this->status, $targetStatus);
    }

    public function isCommitted(): bool
    {
        return BillRunStateMachine::isCommitted($this->status);
    }

    public function isTerminal(): bool
    {
        return BillRunStateMachine::isTerminal($this->status);
    }
}
