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
- Billing foundation shell routes/controllers/requests/services
  - `POST /api/billing/precheck` is real read-only precheck path
  - `POST /api/billing/finalize` is real finalize boundary (transaction + same-month replace semantics)
  - `POST /billing/lock` is real state-transition boundary
  - `POST /billing/approve` is real evidence-aligned 410 removed-flow endpoint
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

## Billing finalize status (this batch)
- `POST /api/billing/finalize` moved from placeholder to real finalize boundary.
- Enforces `month_cycle` (`MM-YYYY`) validation.
- Uses explicit transaction handling (begin/commit/rollback) for finalize writes.
- Implements proven same-month delete-and-replace semantics for:
  - `billing_rows`
  - `logs`
  - `billing_run`
  and upserts `finalized_months`.
- Handles proven duplicate-HR fail-fast (`DUP_HR`) with `409` failed finalize response path.
- Keeps unproven compute internals explicitly marked as draft approximation.

## Remaining blockers before billing implementation
- month-lock exception governance sign-off for domain routes
- lock/approve remain placeholder-only in Laravel
- month-guard currently shell-only (session/config driven), not backed by real month-state domain service
- full finalize compute parity still open (current finalize uses draft compute approximation)

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
