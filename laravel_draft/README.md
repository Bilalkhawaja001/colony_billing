# mbs_project Laravel Draft (LIMITED GO)

Status: **Auth/RBAC runnable foundation** only.

## Implemented in this batch
- Real middleware registration via `bootstrap/app.php`
- Session-based login/logout flow using `auth_users`
- Forced password change gate behavior
- Forgot/reset scaffold with OTP persistence (`auth_password_reset_otp`)
- Profile change-password endpoint with legacy hash-compat verification
- Role middleware for:
  - SUPER_ADMIN
  - BILLING_ADMIN
  - DATA_ENTRY
  - VIEWER
- Minimal protected route groups and blocked-domain stubs
- Auth-only migrations and models

## Intentionally blocked
- Billing, month lifecycle, reconciliation, adjustments, electric_v1
- Reports/exports domain logic

## Unproven/deferred parity notes
- OTP delivery channel is not wired (stored only)
- `APP_KEY` usage for OTP hash is draft assumption (verify against final Flask key policy)
- No non-auth domain behavior implemented

## Local run steps
1. Install PHP 8.2+ and Composer.
2. In `laravel_draft/`:
   - `composer install`
   - `copy .env.example .env` (or `cp .env.example .env`)
   - `php artisan key:generate`
   - `New-Item -ItemType File database\database.sqlite -Force` (Windows)
   - `php artisan migrate`
   - (optional) seed one auth user manually in DB with `pbkdf2_sha256$salt$hex` password format
   - `php artisan serve`
3. Open: `http://127.0.0.1:8000/login`

## Audit references
- `docs/laravel_migration/RBAC_PROOF_MATRIX.md`
- `docs/laravel_migration/BILLING_FINALIZE_PROOF.md`
- `docs/laravel_migration/P0_IMPLEMENTATION_GATE.md`
- `docs/laravel_migration/FINAL_GO_NO_GO.md`
