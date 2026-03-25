<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthUser extends Model
{
    protected $table = 'auth_users';

    protected $fillable = [
        'username', 'email', 'password_hash', 'role', 'is_active', 'force_change_password',
    ];

    protected $casts = [
        'is_active' => 'integer',
        'force_change_password' => 'integer',
    ];
}
