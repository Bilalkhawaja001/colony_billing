# PHASE4_WORKFLOW_BREAKPOINT_MAP

## A) End-to-end trace (non-EV1)

| Workflow | Entry route | Controller | Service method | DB contracts | Guards |
|---|---|---|---|---|---|
| Billing run | `POST /billing/run` | `BillingDraftController::run` | `DraftBillingFlowService::run` | `util_month_cycle`, `util_billing_run`, `util_billing_line`, formula/share tables | `ensure.auth`, `force.password.change`, `role`, `month.guard.shell` |
| Billing finalize | `POST /api/billing/finalize` | `BillingDraftController::finalize` | `DraftBillingFlowService::finalize` | `util_billing_run`, `util_billing_line`, `util_audit_log`, `finalized_months` | same |
| Month transition / lock | `POST /month/open`, `/month/transition`, `/billing/lock` | `ImportsMonthlySetupController`, `BillingDraftController` | `ImportsMonthlySetupService`, `DraftBillingFlowService::lock` | `util_month_cycle`, `util_billing_run` | same |
| Reporting | `GET /reports/*` | `BillingDraftController` | reporting methods in `DraftBillingFlowService` | `util_billing_run`, `util_billing_line`, `util_recovery_payment` | read-role group |
| Exports | `GET /export/excel/*`, `GET /export/pdf/*` | `BillingDraftController` | export methods in `DraftBillingFlowService` | same + XLSX/PDF libs | read-role group |
| Imports / rates (dependency) | `/monthly-rates/*`, `/rates/*`, `/imports/*` | `ImportsMonthlySetupController`, `BillingDraftController` | `ImportsMonthlySetupService`, `DraftBillingFlowService::rates*` | `util_monthly_rates_config`, `util_rate_monthly`, `monthly_variable_expenses` | write-role + month guard |

## B) Breakpoint map (P0/P1)

| Workflow | Breakpoint location | Why fail hot-spot | Severity | Minimal fix applied |
|---|---|---|---|---|
| Billing run | `DraftBillingFlowService::run` | Month can be missing/locked; run could proceed inconsistently | Critical | Added month normalization+validation; explicit missing/LOCKED rejection |
| Billing run | `DraftBillingFlowService::run` | `run_id` could resolve to `0` in bad edge-path | High | Added hard fail if `run_id <= 0` |
| Month lock guard | `config/month_guard.php` | `/billing/run` and compute writes were not in protected list | Critical | Added `/billing/run`, `/billing/elec/compute`, `/billing/water/compute` to protected paths |
| Rates dependency | `DraftBillingFlowService::ratesUpsert/ratesApprove` | Weak month validation, silent approve on missing month rates | High | Added month normalize/validate + 404 on approve when no row updated |
| Request validation | `BillingRunRequest`, `Rates*Request` | Allowed loose/non-normalized month payloads | Medium | Regex hardened (`MM-YYYY` + input-compatible `YYYY-MM`), non-negative rates |
| Policy drift risk | `BillingLockApproveFlowTest` | Approve tests still expected 200 despite intentional 410 policy | High | Tests aligned to explicit removed-flow policy (410) |

## C) Still-open breakpoints (post Phase 4)
- Shell UI modules remain shell by design (Phase 3 triage already documented).
- Full non-EV1 golden-month parity with Flask still pending (Phase 6).
- Docs reconciliation still pending (Phase 7).