# BILLING_ENGINE_LOGIC_AUDIT

## A) Flask proven behavior

### Core run path
- Billing run at `POST /billing/run` builds/rebuilds lines from precomputed shares and formula tables.
- Deletes existing run lines for selected run id before reinsert (`DELETE FROM util_billing_line WHERE billing_run_id=?`).
- Auto-sets run status to `APPROVED` in simplified workflow.
- Evidence: `unified_app/api/app.py:3485-3577`.

### Compute paths
- Electric compute: `/billing/elec/compute` with `zero_attendance_policy` and deterministic split/remainder correction.
- Water compute: `/billing/water/compute` with strict source checks and zone/attendance allocation.
- Evidence: `app.py:3319+`, `3427+`.

### Removed workflows (explicit)
- `/billing/approve`, `/billing/adjustments/create`, `/billing/adjustments/approve`, `/recovery/payment` return HTTP 410.
- Evidence: `app.py:3593-3854`.

## B) Laravel proven behavior

### Draft billing flow
- Centralized in `DraftBillingFlowService` with precheck/finalize/run/reporting.
- `finalize()` executes same-month replace semantics on `util_billing_line` + `util_billing_run` in transaction.
- `run()` exists as active endpoint path.
- Evidence: `laravel_draft/app/Services/Billing/DraftBillingFlowService.php:378+, 1207+`.

### Removed workflows mirrored
- `approve`, `adjustmentCreate`, `adjustmentApprove`, `recoveryPayment` return 410 with parity notes.
- Evidence: `DraftBillingFlowService.php:560-597`.

### Electric V1 engine
- Laravel EV1 orchestration mirrors Python sequence: validate cycle -> load inputs -> validate duplicates -> allocate -> apply adjustments -> replace outputs -> append exceptions/history.
- Evidence: `laravel_draft/app/Services/ElectricV1/OrchestrationService.php:1-220` and `unified_app/api/electric_v1/orchestration_service.py:1-220`.

## C) Proven parity/mismatch decisions

| Logic area | Verdict | Severity | Why |
|---|---|---|---|
| Removed approval/adjustment/payment flows | Parity (both blocked) | Medium | Both implementations explicitly 410. |
| Rerun replace semantics | Partial parity | High | Both do delete/replace patterns, but table contract drift risk remains. |
| EV1 deterministic orchestration | Strong parity | Medium | Sequence and dataflow strongly aligned across repos. |
| Full legacy utility billing engine parity (non-EV1) | Not fully proven | Critical | Laravel uses draft service + schema assumptions; Flask has mixed table contracts. |

## D) Critical findings
1. **Contract drift in billing table naming/use** can invalidate rerun/replace assumptions.
   - Flask DDL section defines `billing_run`/`billing_rows`, while operational code uses `util_billing_run`/`util_billing_line`.
   - Evidence: `app.py:4337+` vs `app.py:3485+`.
2. **Laravel depends on util billing tables not created by included migrations**, causing environment-dependent behavior.
   - Evidence: `DraftBillingFlowService.php` SQL references vs no matching migration files.

## E) Engine readiness conclusion
- EV1 path looks near production-grade from code structure/tests.
- Broader billing engine parity is **not fully hardened** due DB-contract ambiguity and draft-service assumptions.
- Release classification for full billing replacement: **NO-GO**.