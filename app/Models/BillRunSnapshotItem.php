<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillRunSnapshotItem extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'bill_run_snapshot_items';

    protected $fillable = [
        'snapshot_id',
        'entity_type',
        'entity_id',
        'sort_order',
        'row_hash',
        'payload_json',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    public function snapshot()
    {
        return $this->belongsTo(BillRunSnapshot::class, 'snapshot_id');
    }

    public function payload(): array
    {
        $decoded = json_decode((string) $this->payload_json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
