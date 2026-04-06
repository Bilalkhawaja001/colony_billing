# FLASK_COMPLETE_MODULE_SOURCE_MAP

Purpose: Flask source truth map (product/module depth), route parity nahi.

## A) Operator UI modules in Flask (REAL)

| Module | Primary UI route | Template | Core backend routes/workflows |
|---|---|---|---|
| Dashboard | `/ui/dashboard` | `unified_app/templates/dashboard_futuristic.html` | `/api/dashboard/colony-kpis`, `/api/dashboard/family-members` |
| Billing workspace | `/ui/billing` | `billing.html` | `/billing/elec/compute`, `/billing/water/compute`, `/billing/run`, `/billing/lock`, `/billing/fingerprint` |
| Month cycle/governance | `/ui/month-cycle`, `/ui/month-control` | `month.html` | `/month/open`, `/month/transition` |
| Rates | `/ui/rates` | `rates.html` | `/rates/upsert`, `/rates/approve`, `/monthly-rates/*` |
| Imports | `/ui/imports`, `/ui/meter-register-ingest` | `imports.html`, `meter_register_ingest.html` | `/imports/meter-register/ingest-preview`, `/imports/mark-validated`, `/imports/error-report/<token>` |
| Reports | `/ui/reports` | `reports.html` | `/reports/monthly-summary`, `/reports/recovery`, `/reports/employee-bill-summary`, `/reports/van`, `/reports/elec-summary` |
| Reconciliation | `/ui/reconciliation` | `reconciliation.html` | `/reports/reconciliation`, `/export/excel/reconciliation` |
| Exports | (from reports/reconciliation) | n/a | `/export/excel/monthly-summary`, `/export/pdf/monthly-summary` |
| Employee master | `/ui/employee-master`, `/ui/employees`, `/ui/employee-helper` | `employee_master.html`, `employees.html`, `employee_helper.html` | `/employees*`, `/registry/employees/*` |
| Unit master | `/ui/unit-master` | `unit_master.html` | `/units*`, `/api/units/reference*` |
| Room master | `/ui/rooms` | `rooms.html` | `/rooms*` |
| Occupancy | `/ui/occupancy` | `occupancy.html` | `/occupancy*`, `/api/occupancy/autofill` |
| Meter master/readings | `/ui/meter-master` | `meter_master.html` | `/meter-reading/*`, `/meter-unit*` |
| Water modules | `/ui/water-meters` | `water_meters.html` | `/api/water/occupancy-snapshot`, `/api/water/zone-adjustments`, `/api/water/allocation-preview` |
| Van module | `/ui/van` | `van.html` | `/reports/van`, monthly variable expense hooks |
| Family details | `/ui/family-details` | `family_details.html` | `/family/details/context`, `/family/details`, `/family/details/upsert` |
| Results & logs | `/ui/results/employee-wise`, `/ui/results/unit-wise`, `/ui/logs`, `/ui/finalized-months` | templates exist | `/api/results/*`, `/api/logs`, finalize flows |
| EV1 run/outputs | `/ui/electric-v1-run`, `/ui/electric-v1-outputs` | `electric_v1_run.html`, `electric_v1_outputs.html` | `/api/electric-v1/run`, `/api/electric-v1/outputs` |

## B) Intentionally removed flows in Flask (BLOCKED by design)

| Flow | Route | Status |
|---|---|---|
| Billing approve | `/billing/approve` | 410 |
| Adjustment create | `/billing/adjustments/create` | 410 |
| Adjustment approve | `/billing/adjustments/approve` | 410 |
| Recovery payment | `/recovery/payment` | 410 |

## C) Source contract behavior notes (important)
- Active non-EV1 runtime contract is util-prefixed tables (`util_*`) in run/report/lock paths.
- Flask run sequence currently inserts ELEC then clears run lines, then re-inserts WATER/DRINKING/SCHOOL_VAN (authoritative current behavior).

## D) Source evidence anchors
- Main route/workflow source: `unified_app/api/app.py`
- UI templates: `unified_app/templates/*.html`
- EV1 orchestration source: `unified_app/api/electric_v1/*`