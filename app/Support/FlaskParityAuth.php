<?php

namespace App\Support;

class FlaskParityAuth
{
    public static function verifyPassword(string $password, string $stored): bool
    {
        try {
            [$algo, $salt, $hexDigest] = explode('$', $stored, 3);
            if ($algo !== 'pbkdf2_sha256') {
                return false;
            }

            $chk = hash_pbkdf2('sha256', $password, $salt, 120000, 0, false);
            return hash_equals($hexDigest, $chk);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function passwordHash(string $password, ?string $salt = null): string
    {
        $salt = $salt ?: bin2hex(random_bytes(16));
        $digest = hash_pbkdf2('sha256', $password, $salt, 120000, 0, false);
        return "pbkdf2_sha256$".$salt."$".$digest;
    }

    public static function otpHash(int $userId, string $otp, string $key): string
    {
        return hash_hmac('sha256', $userId.':'.$otp, $key);
    }
}
