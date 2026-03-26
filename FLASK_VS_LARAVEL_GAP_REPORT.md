# FLASK_VS_LARAVEL_GAP_REPORT

## Audit scope
- **Flask source audited (reference):**
  - `mbs_project/batchA_app_core/api/app.py`
  - `mbs_project/batchC_reports_exports/api/reports_api.py`
- **Laravel app audited (current):**
  - `routes/web.php`
  - `app/Http/Controllers/Auth/AuthDraftController.php`
  - `app/Http/Controllers/Billing/BillingDraftController.php`
  - `app/Services/Billing/DraftBillingFlowService.php`
  - `app/Http/Middleware/*`
  - `resources/views/**/*`
  - `README.md` (`LIMITED GO` status)

## Executive finding
Laravel is **not near parity** with Flask reference behavior yet. It has added auth/RBAC shell + some billing/report surfaces, but multiple Flask workflows are missing, partially ported, or behavior-mismatched.

---

## Gap register (strict)

### 1) [CLOSED] Billing run contract mismatch (`/billing/run` missing)
- **Severity:** Critical
- **Module:** Billing core
- **Flask evidence:** `batchA_app_core/api/app.py` defines `POST /billing/run` with idempotent `run_key` handling and utility line upsert.
- **Laravel current state:** No `/billing/run`; replaced with `/api/billing/precheck` + `/api/billing/finalize`.
- **Exact gap:** API contract not parity-compatible for clients expecting `/billing/run` + `run_key` idempotency behavior.
- **User impact:** Existing integrations/automation can break.
- **Recommended fix direction:** Add compatibility endpoint or strict migration adapter preserving Flask request/response semantics.
- **Launch blocking:** Yes
- **Status:** Mismatched

### 2) [CLOSED] Rates lifecycle APIs missing
- **Severity:** Critical
- **Module:** Rates governance
- **Flask evidence:** `POST /rates/upsert`, `POST /rates/approve` in `batchA_app_core/api/app.py`.
- **Laravel current state:** No equivalent routes/controllers.
- **Exact gap:** Monthly rate setup/approval workflow absent.
- **User impact:** Billing correctness and month operations blocked.
- **Recommended fix direction:** Implement same contract + validations + approval audit chain.
- **Launch blocking:** Yes
- **Status:** Missing

### 3) Import validation endpoint missing
- **Severity:** High
- **Module:** Import pipeline
- **Flask evidence:** `POST /imports/mark-validated` exists in Flask batchA API.
- **Laravel current state:** No equivalent endpoint.
- **Exact gap:** Batch validation state cannot be marked via parity route.
- **User impact:** ETL/state machine tooling gaps.
- **Recommended fix direction:** Implement endpoint and status transitions with audit.
- **Launch blocking:** Likely
- **Status:** Missing

### 4) [CLOSED] Billing approve semantics changed to HTTP 410
- **Severity:** Critical
- **Module:** Billing approval
- **Flask evidence:** `POST /billing/approve` updates run status to APPROVED.
- **Laravel current state:** `approve()` returns HTTP 410 intentionally removed.
- **Exact gap:** Active Flask flow disabled in Laravel.
- **User impact:** Approval step unavailable.
- **Recommended fix direction:** Either restore approval flow or publish migration-breaking change with replacement path and tooling.
- **Launch blocking:** Yes
- **Status:** Mismatched

### 5) Month transition payload parity mismatch
- **Severity:** High
- **Module:** Month cycle
- **Flask evidence:** `/month/transition` accepts `to_state` and updates target state.
- **Laravel current state:** Route returns fixed shell response, does not apply `to_state` state machine in route closure.
- **Exact gap:** State transition behavior not parity-equal at route level.
- **User impact:** Cycle governance unreliable for parity clients.
- **Recommended fix direction:** Port real transition logic from Flask contract to domain service.
- **Launch blocking:** Yes
- **Status:** Partial

### 6) Month open behavior not equivalent
- **Severity:** High
- **Module:** Month cycle
- **Flask evidence:** `INSERT OR IGNORE` into `util_month_cycle` on `/month/open`.
- **Laravel current state:** route closure returns `guard-shell-pass` response without explicit insert behavior.
- **Exact gap:** creation semantics differ.
- **User impact:** Month may not exist when expected.
- **Recommended fix direction:** Implement same upsert/open behavior with validation.
- **Launch blocking:** Yes
- **Status:** Partial

### 7) Report export parity mismatch (monthly summary CSV/PDF)
- **Severity:** High
- **Module:** Reports/exports
- **Flask evidence:** `/export/excel/monthly-summary` (CSV), `/export/pdf/monthly-summary`.
- **Laravel current state:** Only `/export/excel/reconciliation` (XLSX).
- **Exact gap:** Flask export surfaces missing.
- **User impact:** Reporting pipeline incompleteness.
- **Recommended fix direction:** Add monthly-summary CSV/PDF parity routes.
- **Launch blocking:** Potentially
- **Status:** Missing

### 8) Recovery report source mismatch risk
- **Severity:** Medium
- **Module:** Reports
- **Flask evidence:** recovery report aggregates billed amounts from `util_billing_line` by month.
- **Laravel current state:** similar query exists; however depends on Laravel finalize output model not identical to Flask run source tables.
- **Exact gap:** Potential divergence due upstream billing run differences.
- **User impact:** Report discrepancies.
- **Recommended fix direction:** golden dataset parity tests.
- **Launch blocking:** No (if monitored)
- **Status:** Needs verification

### 9) UI parity: Flask ops UI not ported
- **Severity:** Critical
- **Module:** Frontend
- **Flask evidence:** source reference audited is API-first; production Flask app likely has operational UI not represented in Laravel beyond shell pages.
- **Laravel current state:** blades are admin shell/workspace pages with limited data binding.
- **Exact gap:** functional operator screens/tables/filters/actions are largely absent.
- **User impact:** Users can access pages but cannot complete full operational tasks.
- **Recommended fix direction:** Build module UIs with data grids/forms bound to real endpoints.
- **Launch blocking:** Yes
- **Status:** Partial

### 10) [CLOSED] Hidden helper/formula pipelines absent
- **Severity:** High
- **Module:** Billing compute
- **Flask evidence:** billing run consumes `util_formula_result`, `util_drinking_formula_result`, `util_school_van_monthly_charge`.
- **Laravel current state:** finalize uses alternative draft engine over `hr_input/map_room/readings/ro_drinking`.
- **Exact gap:** source dataflow changed; helper formula pipelines not parity.
- **User impact:** compute differences and reconciliation variance.
- **Recommended fix direction:** port exact compute graph or provide tested equivalence proof.
- **Launch blocking:** Yes
- **Status:** Mismatched

### 11) Month format contract mismatch risk
- **Severity:** Medium
- **Module:** Validation
- **Flask evidence:** batch APIs often use `YYYY-MM` examples; Laravel validators enforce `MM-YYYY`.
- **Laravel current state:** strict regex `/^\d{2}-\d{4}$/`.
- **Exact gap:** potential payload incompatibility for existing clients.
- **User impact:** request validation failures.
- **Recommended fix direction:** support both formats with canonical normalization or explicit migration tooling.
- **Launch blocking:** Possibly
- **Status:** Mismatched

### 12) Reconciliation behavior not Flask-evidenced in source set
- **Severity:** Medium
- **Module:** Reconciliation
- **Flask evidence:** no reconciliation endpoint in audited Flask batchA/C source.
- **Laravel current state:** custom `/reports/reconciliation` + `/export/excel/reconciliation`.
- **Exact gap:** cannot confirm parity; likely net-new approximation.
- **User impact:** unknown trust level.
- **Recommended fix direction:** define canonical reconciliation spec and backtest.
- **Launch blocking:** No (if signed off)
- **Status:** Needs verification

### 13) Auth/reset/change-password parity cannot be proven from Flask source set
- **Severity:** High
- **Module:** Auth
- **Flask evidence:** audited Flask source set does not include full auth/reset service equivalent.
- **Laravel current state:** implemented in `AuthDraftController` with OTP scaffold.
- **Exact gap:** no source-evidence parity proof.
- **User impact:** uncertain behavior differences.
- **Recommended fix direction:** compare against production Flask auth codebase (not batchA/C) and add parity tests.
- **Launch blocking:** Potentially
- **Status:** Needs verification

### 14) Disabled legacy paths remain unresolved at migration level
- **Severity:** Medium
- **Module:** Backward compatibility
- **Flask evidence:** active endpoints existed in source batch APIs.
- **Laravel current state:** some endpoints return 410 by design.
- **Exact gap:** migration strategy not complete for client cutover.
- **User impact:** existing consumers fail without adapter.
- **Recommended fix direction:** deprecation plan + compatibility shims.
- **Launch blocking:** Yes for old clients
- **Status:** Intentional change

---

## Critical closure update (this batch)
- Closed: Billing run contract mismatch (`/billing/run` restored)
- Closed: Rates lifecycle missing (`/rates/upsert`, `/rates/approve` restored)
- Closed: Billing approve mismatch (`/billing/approve` active)
- Closed: Compute/dataflow mismatch for run path (formula-result pipeline restored in `/billing/run`)

## Totals by severity (after this batch)
- **Critical:** 0
- **High:** 6
- **Medium:** 4
- **Low:** 0

## Top 10 most dangerous missing items
1. Missing `/billing/run` parity contract
2. Missing rates upsert/approve APIs
3. `/billing/approve` disabled (410)
4. Month transition behavior not parity stateful
5. Month open behavior not parity insert/upsert
6. Missing monthly-summary CSV/PDF exports
7. Compute/dataflow mismatch vs formula-result pipeline
8. Frontend still operationally shallow vs production expectations
9. Missing import validation API
10. Auth parity unproven against production Flask auth source

## Pages that look complete but are functionally incomplete
- `/ui/billing`
- `/ui/month-cycle`
- `/ui/reports`
- `/ui/reconciliation`

## Routes that exist but are behavior-weaker than Flask
- `/month/open`
- `/month/transition`
- `/billing/approve`

## Final verdict
**Not near parity**
