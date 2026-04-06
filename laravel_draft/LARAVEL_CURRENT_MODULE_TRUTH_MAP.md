# LARAVEL_CURRENT_MODULE_TRUTH_MAP

Classification key: REAL / PARTIAL / SHELL / BLOCKED / MISSING

## A) UI/module truth (Laravel current)

| Module | Laravel route/page | Current truth | Evidence |
|---|---|---|---|
| Dashboard | `/ui/dashboard` | REAL/PARTIAL | `ui.dashboard` + dashboard service-backed KPIs |
| Reports page | `/ui/reports` | REAL/PARTIAL | `ui.reports` |
| Reconciliation page | `/ui/reconciliation` | REAL/PARTIAL | `ui.reconciliation` |
| Month control | `/ui/month-control` | REAL/PARTIAL | `ui.month-control` |
| Billing workspace | `/ui/billing` | SHELL | `ParityUiController::billing -> renderUiPage` |
| Month cycle page | `/ui/month-cycle` | SHELL | same pattern |
| Imports page | `/ui/imports` | SHELL | same pattern |
| Rates page | `/ui/rates` | SHELL | same pattern |
| Water meters page | `/ui/water-meters` | SHELL | same pattern |
| Van page | `/ui/van` | SHELL | same pattern |
| Employee master page | `/ui/employee-master` | SHELL | same pattern |
| Employees page | `/ui/employees` | SHELL | same pattern |
| Employee helper page | `/ui/employee-helper` | SHELL | same pattern |
| Unit master page | `/ui/unit-master` | SHELL | same pattern |
| Meter master page | `/ui/meter-master` | SHELL | same pattern |
| Meter ingest page | `/ui/meter-register-ingest` | SHELL | same pattern |
| Rooms page | `/ui/rooms` | SHELL | same pattern |
| Occupancy page | `/ui/occupancy` | SHELL | same pattern |
| Elec summary page | `/ui/elec-summary` | SHELL | same pattern |
| Masters/* pages | `/ui/masters/*` | SHELL | same pattern |
| Inputs/* pages | `/ui/inputs/*` | SHELL | same pattern |
| Finalized months page | `/ui/finalized-months` | SHELL | same pattern |
| Family details | `/ui/family-details` | PARTIAL | dedicated blade exists but depth unknown vs Flask |
| Results employee/unit | `/ui/results/*` | PARTIAL | dedicated blade exists |
| Logs | `/ui/logs` | PARTIAL | dedicated blade exists |
| EV1 UI pages | `/ui/electric-v1-run`, `/ui/electric-v1-outputs` | PARTIAL/REAL (backend richer than UI) | EV1 routes + service exist; UI route in current controller file mapped to shell helper |

## B) Workflow/API truth (non-EV1)

| Workflow | Current truth |
|---|---|
| Billing run | REAL (hardened), parity-fixed for persisted 3-line behavior |
| Billing finalize | REAL (hardened) |
| Month open/transition/lock guard | REAL/PARTIAL (service-level real, UI governance depth not full) |
| Reports APIs | REAL |
| Export APIs | REAL |
| Rates/import dependencies | REAL/PARTIAL (service-level real, page-level shell) |
| Approve/adjustments/recovery payment | BLOCKED intentionally (410) |

## C) DB contract truth
- util/month/rate/billing tables now migration-owned.
- non-EV1 core workflows can boot on fresh bootstrap.
- Flask authoritative oddity (persisted 3-line final set) now matched in Laravel run path.

## D) Why this is not full product parity yet
- Operator-facing module pages are mostly shell for many key modules.
- Flask-like module UX depth (interactive forms, grids, operator actions) not rebuilt page-by-page.