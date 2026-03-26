# ROUTE_PARITY_DIFF

## Route parity table

| Flask Route | Method | Laravel Equivalent | Same Method | Same Auth | Same Role Guard | Same Response Type | Same Side Effects | Parity Result |
|---|---|---|---|---|---|---|---|---|
| `/month/open` | POST | `/month/open` | Yes | No (Flask open API) | No | JSON yes | **No** (Flask writes row; Laravel shell route) | Partial |
| `/month/transition` | POST | `/month/transition` | Yes | No | No | JSON yes | **No** (Flask applies `to_state`; Laravel pass-mode route) | Partial |
| `/rates/upsert` | POST | `/rates/upsert` | Yes | No | No | JSON yes | Yes | Closed |
| `/rates/approve` | POST | `/rates/approve` | Yes | No | No | JSON yes | Yes | Closed |
| `/imports/mark-validated` | POST | N/A | N/A | N/A | N/A | N/A | N/A | Missing |
| `/billing/run` | POST | `/billing/run` | Yes | No | No | JSON yes | Yes (formula-result pipeline + run_key idempotency) | Closed |
| `/billing/approve` | POST | `/billing/approve` | Yes | No | No | JSON yes | Yes | Closed |
| `/billing/lock` | POST | `/billing/lock` | Yes | No | No | JSON yes | Partial (Laravel has stricter guard/state checks) | Intentional change |
| `/reports/monthly-summary` | GET | `/reports/monthly-summary` | Yes | No | No | JSON yes | Partial (run resolution differs) | Partial |
| `/reports/recovery` | GET | `/reports/recovery` | Yes | No | No | JSON yes | Partial | Partial |
| `/reports/van` | GET | `/reports/van` | Yes | No | No | JSON yes | Partial | Partial |
| `/export/excel/monthly-summary` | GET | N/A | N/A | N/A | N/A | N/A | N/A | Missing |
| `/export/pdf/monthly-summary` | GET | N/A | N/A | N/A | N/A | N/A | N/A | Missing |

## Laravel-only routes (no direct Flask batchA/C parity evidence)
- `/login`, `/logout`, `/forgot-password`, `/reset-password`
- `/ui/dashboard`, `/ui/billing`, `/ui/month-cycle`, `/ui/reports`, `/ui/reconciliation`
- `/api/billing/precheck`
- `/reports/reconciliation`
- `/reports/employee-bill-summary`
- `/reports/elec-summary`
- `/export/excel/reconciliation`

These routes are either net-new, migrated abstractions, or require production Flask source verification.

## Route-level high-risk diffs
1. Billing contract pivot from `/billing/run` to `/api/billing/finalize`
2. Approval route behavior hard-disabled (410)
3. Missing rates and import lifecycle endpoints
4. Missing monthly-summary export endpoints
5. Month transition/open side effects not guaranteed parity
