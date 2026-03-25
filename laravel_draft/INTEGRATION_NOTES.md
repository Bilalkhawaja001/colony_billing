# Integration Notes (LIMITED GO batch)

This draft is now structured as a runnable Laravel app layer (pending PHP/Composer availability on host).

## Middleware wiring (implemented)
In `bootstrap/app.php` aliases:
- `ensure.auth` => `App\Http\Middleware\EnsureAuthenticated::class`
- `force.password.change` => `App\Http\Middleware\ForcePasswordChange::class`
- `role` => `App\Http\Middleware\RoleGate::class`

## Session parity fields wired
- `user_id`
- `role`
- `admin_user_id`
- `actor_user_id`
- `force_change_password`

## Auth tables added
- `auth_users`
- `auth_password_reset_otp`
- `auth_audit_log`

## Explicitly not implemented
- billing/month/reconciliation/adjustments/electric_v1
- report/export domain logic
- OTP transport delivery
