<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthAuditLog extends Model
{
    protected $table = 'auth_audit_log';

    public const UPDATED_AT = null;

    protected $fillable = ['event_type', 'username_hint', 'user_id', 'outcome', 'details_json'];
}
