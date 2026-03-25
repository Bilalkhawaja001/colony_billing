# Integration Notes (Draft)

Because full Laravel runtime is not bootstrapped in this workspace, these aliases must be wired when integrating into real Laravel app:

In `app/Http/Kernel.php` route middleware:
- `ensure.auth` => `App\Http\Middleware\EnsureAuthenticated::class`
- `force.password.change` => `App\Http\Middleware\ForcePasswordChange::class`
- `role` => `App\Http\Middleware\RoleGate::class`

## Proven parity mapped
- Public auth endpoints: `/login`, `/logout`, `/forgot-password`, `/reset-password`
- Forced password change gate redirect to `/ui/profile`
- API unauthorized/forbidden JSON shape retained as draft target

## Explicitly unproven/deferred
- Real DB auth provider integration
- OTP persistence/attempt lock
- Password hash verify compatibility layer
- Any billing/month/reconciliation behavior
