<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillRunPreflightCheck extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'bill_run_preflight_checks';

    protected $fillable = [
        'bill_run_id',
        'check_code',
        'severity',
        'status',
        'title',
        'message',
        'entity_type',
        'entity_id',
        'source_table',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function billRun()
    {
        return $this->belongsTo(BillRun::class, 'bill_run_id');
    }

    public function isStopping(): bool
    {
        return $this->severity === 'stop' && $this->status !== 'pass';
    }
}
