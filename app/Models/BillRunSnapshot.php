<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillRunSnapshot extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'bill_run_snapshots';

    protected $fillable = [
        'bill_run_id',
        'snapshot_type',
        'month_cycle',
        'cycle_start_date',
        'cycle_end_date',
        'source_table',
        'source_filter_hash',
        'row_count',
        'snapshot_hash',
        'summary_json',
    ];

    protected $casts = [
        'cycle_start_date' => 'date',
        'cycle_end_date' => 'date',
        'row_count' => 'integer',
        'summary_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function billRun()
    {
        return $this->belongsTo(BillRun::class, 'bill_run_id');
    }

    public function items()
    {
        return $this->hasMany(BillRunSnapshotItem::class, 'snapshot_id');
    }
}
