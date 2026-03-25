# mbs_project Laravel Draft (LIMITED GO)

Status: **Auth/RBAC runnable foundation (hardened)** only.

## Implemented
- Real middleware registration in `bootstrap/app.php`
- Session login/logout with Flask-compatible password verify (`pbkdf2_sha256$salt$hex`)
- Forced password-change gate
- OTP reset scaffold with lifecycle hardening:
  - expiry check
  - max attempts check
  - single-use (`used_at`)
  - old active OTPs invalidated on resend
- Auth audit events (login success/fail, reset flow, password change, logout)
- RBAC truth-table enforcement for auth shell routes via `shell.rbac`
- Month-guard shell middleware (`month.guard.shell`) with config-driven lock state + exception policy
- Billing foundation shell routes/controllers/requests/services (draft-only, no financial logic)
- Dev auth tooling commands:
  - `php artisan mbs:auth:hash {password}`
  - `php artisan mbs:auth:user-create {username} {email} {password} {role}`
- Feature tests for auth and month-guard shell behavior

## Session keys written after login
- `user_id`
- `role`
- `admin_user_id`
- `actor_user_id`
- `force_change_password`

## Still intentionally blocked
- billing, month lifecycle, reconciliation, adjustments, electric_v1
- reports/exports domain logic

## Month-guard shell behavior (non-billing)
- **Locked month + protected write route** (`/month/open`, `/billing/lock`, `/api/billing/precheck`): blocked with `423 month locked`
- **Unlocked month + protected write route**: allowed through shell route stack
- **Exception route configured** (`/month/transition`, `/api/billing/finalize`): allowed even when locked
- **Unauthorized role** (`DATA_ENTRY` on billing shell routes): denied by role middleware (`403`) before month-guard
- **Unauthenticated API**: blocked with `401` before role/month-guard

## Remaining blockers before billing implementation
- month-lock exception governance sign-off for domain routes
- no billing/finalize/reconciliation/adjustment controllers yet (intentionally blocked)
- month-guard currently shell-only (session/config driven), not backed by real month-state domain service

## Remaining unproven/deferred parity
- OTP delivery transport (SMS/email) not wired
- OTP signing key policy may need strict Flask-aligned finalization
- Login brute-force lockout not proven in Flask evidence (not enforced here)

## Local verification steps
1. Install PHP 8.2+ and Composer.
2. In `laravel_draft/`:
   - `composer install`
   - `copy .env.example .env`
   - `php artisan key:generate`
   - `New-Item -ItemType Directory -Force database | Out-Null`
   - `New-Item -ItemType File database\database.sqlite -Force | Out-Null`
   - `php artisan migrate`
3. Create test users:
   - `php artisan mbs:auth:user-create admin admin@example.com Admin123! SUPER_ADMIN --force-change=0`
   - `php artisan mbs:auth:user-create entry entry@example.com Entry123! DATA_ENTRY --force-change=1`
4. Run app:
   - `php artisan serve`
   - open `http://127.0.0.1:8000/login`
5. Run tests:
   - `php artisan test --filter=AuthFoundationTest`
