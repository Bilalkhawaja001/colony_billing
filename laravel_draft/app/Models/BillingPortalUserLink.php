<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPortalUserLink extends Model
{
    protected $fillable = [
        'portal_user_id',
        'billing_user_id',
        'module_key',
        'billing_role',
        'is_active',
    ];

    protected $casts = [
        'portal_user_id' => 'integer',
        'billing_user_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function billingUser(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'billing_user_id');
    }
}
