<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthPasswordResetOtp extends Model
{
    protected $table = 'auth_password_reset_otp';

    protected $fillable = [
        'user_id', 'otp_hash', 'expires_at', 'attempts', 'used_at', 'last_sent_at',
    ];

    public $timestamps = false;
}
