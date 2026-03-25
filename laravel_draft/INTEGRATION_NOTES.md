# Integration Notes (LIMITED GO batch)

This draft is now structured as a runnable Laravel app layer (pending PHP/Composer availability on host).

## Middleware wiring (implemented)
In `bootstrap/app.php` aliases:
- `ensure.auth` => `App\Http\Middleware\EnsureAuthenticated::class`
- `force.password.change` => `App\Http\Middleware\ForcePasswordChange::class`
- `role` => `App\Http\Middleware\RoleGate::class`
- `shell.rbac` => `App\Http\Middleware\ShellPathRbac::class`
- `month.guard.shell` => `App\Http\Middleware\MonthGuardShell::class`

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

## Month-guard plug-in plan for future domain controllers
- Keep `month.guard.shell` on write routes until real MonthState service is implemented.
- Replace config/session lock source with domain-backed month state resolver.
- Keep exception list explicit and policy-approved before enabling real month/billing controllers.

## Billing foundation shell boundaries
- Routes:
  - `POST /api/billing/precheck` (**real read-only precheck implemented**)
  - `POST /api/billing/finalize` (**real finalize boundary implemented**)
  - `POST /billing/lock` (**real state-transition boundary implemented**)
  - `POST /billing/approve` (**real 410 removed-flow parity behavior implemented**)
  - `POST /billing/adjustments/create` (**real 410 removed-flow parity behavior implemented**)
  - `POST /billing/adjustments/approve` (**real 410 removed-flow parity behavior implemented**)
  - `POST /recovery/payment` (**real 410 disabled-flow parity behavior implemented**)
  - `GET /reports/reconciliation` (**real read-only reconciliation report implemented**)
- Controller: `BillingDraftController`
- Validation:
  - precheck expects `month_cycle` in `MM-YYYY` format
  - finalize expects `month_cycle` in `MM-YYYY` format
- Service boundary contract: `BillingFlowContract`
- Current behavior:
  - precheck reads source tables and returns parity-shaped response
  - finalize executes txn + same-month replace writes and returns Flask-aligned status keys
  - lock enforces proven APPROVED->LOCKED transition with month APPROVAL guard
  - approve/adjustment create/adjustment approve/recovery payment return evidence-aligned 410 behavior
  - reconciliation returns read-only parity response shape (`summary`, `by_utility`, `by_employee`, `notes`)
- Explicit non-parity note:
  - finalize computation internals currently use draft approximation pending full evidence-complete engine port
  - legacy unreachable code below Flask 410 endpoints (adjustment/recovery) remains intentionally unported

## Explicitly not implemented
- billing/month/reconciliation/adjustments/electric_v1 domain logic
- report/export domain logic
- OTP transport delivery
