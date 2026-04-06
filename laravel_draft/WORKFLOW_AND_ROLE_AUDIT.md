# WORKFLOW_AND_ROLE_AUDIT

## 1) Workflow audit (Flask vs Laravel)

### Workflow: Run billing
- Flask: compute prerequisites -> `/billing/run` -> lines inserted -> run marked APPROVED.
  - Evidence: `unified_app/api/app.py:3319,3427,3485-3577`.
- Laravel: `/billing/run` active in `DraftBillingFlowService`.
  - Evidence: `routes/web.php`, `DraftBillingFlowService.php:1207+`.
- Verdict: **Partial parity** (critical dependency on non-migrated util tables in Laravel).

### Workflow: Finalize cycle
- Flask: `/api/billing/finalize` active.
- Laravel: finalize transaction exists and does same-month replace behavior.
- Verdict: **Partial parity**.

### Workflow: Approve / adjustments / recovery
- Both stacks return 410 intentionally.
- Verdict: **Parity but functionally removed**.

### Workflow: Month open/transition
- Flask has active state transitions with audit.
- Laravel has service endpoints and month guard middleware; effectiveness depends on util month tables existing.
- Verdict: **Partial parity**.

## 2) Role and access audit

### Laravel role controls
- `RoleGate` enforces explicit allowed roles.
- `ShellPathRbac` enforces path-role map and viewer write-block for `/api/*` mutating methods.
- `MonthGuardShell` blocks protected writes when month locked.
- Evidence: `app/Http/Middleware/RoleGate.php`, `ShellPathRbac.php`, `MonthGuardShell.php`.

### Flask role controls
- `require_roles`, `require_admin_from_request`, and `before_request` path-role enforcement.
- Viewer API mutation blocked.
- Evidence: `unified_app/api/app.py` auth/guard section.

## 3) Operational breaks / risk
1. UI shell routes may be role-accessible but operationally shallow.
2. Billing/report transitions depend on util tables not guaranteed by Laravel migrations.
3. Path-level parity includes literal legacy patterns (`<id>` compatibility routes) in Laravel; functional depth differs behind route.

## 4) Verdict
Role middleware parity is broadly good; workflow parity is incomplete for full production because module depth and schema guarantees are not fully closed.