# WORKFLOW_PARITY_DIFF

## Workflow comparison

### 1) Login / reset / change password
- **Flask evidence:** Not available in audited batchA/C source.
- **Laravel state:** Implemented (`AuthDraftController`) with session keys, OTP reset scaffold, password change API.
- **Parity:** **Needs verification** against actual Flask auth source.
- **Severity:** High

### 2) Dashboard navigation
- **Flask evidence:** API batches do not define dashboard UI.
- **Laravel state:** Dashboard blade + navigation exists.
- **Parity:** Partial (UI exists, but not evidence-comparable).
- **Severity:** Medium

### 3) Billing precheck
- **Flask evidence:** No explicit `/api/billing/precheck` route in batchA/C.
- **Laravel state:** Present and active.
- **Parity:** Intentional change / enhancement (not directly comparable).
- **Severity:** Low

### 4) Billing finalize/run
- **Flask evidence:** `POST /billing/run` builds lines from formula result tables with `run_key` idempotency.
- **Laravel state:** `POST /billing/run` implemented with formula-result pipeline and idempotent run-key semantics.
- **Gap:** Closed for critical parity path.
- **Parity:** Closed.
- **Severity:** Critical (resolved)

### 5) Billing approve
- **Flask evidence:** Active approve endpoint sets run APPROVED.
- **Laravel state:** `/billing/approve` implemented as active status transition.
- **Parity:** Closed.
- **Severity:** Critical (resolved)

### 6) Billing lock
- **Flask evidence:** Simple lock update.
- **Laravel state:** lock requires existing run, APPROVED status, month state APPROVAL.
- **Parity:** Intentional stricter guard.
- **Severity:** Medium

### 7) Month cycle workflow
- **Flask evidence:** open + transition with explicit state updates.
- **Laravel state:** shell pass routes and month guard middleware; state transitions not fully equivalent.
- **Parity:** Partial.
- **Severity:** High

### 8) Reports workflow
- **Flask evidence:** monthly-summary/recovery/van + CSV/PDF exports.
- **Laravel state:** monthly-summary/recovery/van present; CSV/PDF monthly-summary exports missing; extra reports added.
- **Parity:** Partial.
- **Severity:** High

### 9) Reconciliation workflow
- **Flask evidence:** Not in audited batchA/C.
- **Laravel state:** custom reconciliation endpoint + XLSX export.
- **Parity:** Needs verification (net-new).
- **Severity:** Medium

### 10) Adjustments / recovery payment
- **Flask evidence:** Not in batchA/C APIs (broader legacy evidence suggests prior flows existed in earlier app generations).
- **Laravel state:** 410 disabled.
- **Parity:** Intentional change requiring policy sign-off.
- **Severity:** Medium

### 11) Exports workflow
- **Flask evidence:** monthly summary CSV + PDF available.
- **Laravel state:** only reconciliation XLSX export.
- **Parity:** Missing.
- **Severity:** High

### 12) Data flow and hidden helper logic
- **Flask evidence:** uses `util_formula_result`, `util_drinking_formula_result`, `util_school_van_monthly_charge` as billing inputs.
- **Laravel state:** uses `hr_input`, `map_room`, `readings`, `ro_drinking` via draft engine.
- **Parity:** Mismatched compute graph.
- **Severity:** Critical

## Workflow verdict
Core workflow parity is **partial to mismatched**; billing approval/run and export coverage are the most severe gaps.
