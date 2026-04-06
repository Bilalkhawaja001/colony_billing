# BILLING_ROUTE_PAGE_PARITY_MATRIX

Legend: **F**=Fully working, **P**=Partial, **M**=Missing, **B**=Intentionally blocked, **S**=Shell/placeholder

| Domain | Flask (source) | Laravel (target) | Status | Severity | Evidence |
|---|---|---|---|---|---|
| Billing UI | `GET /ui/billing` real template (`templates/billing.html`) | `GET /ui/billing` -> `renderUiPage()` generic shell | **S (Laravel)** | High | `unified_app/api/app.py:1350`; `laravel_draft/app/Http/Controllers/Ui/ParityUiController.php:81-83`; `resources/views/ui/page.blade.php:6` |
| Month cycle UI | `GET /ui/month-cycle` real page (`templates/month.html`) | `/ui/month-cycle` shell page | **S (Laravel)** | High | `app.py:1317`; `ParityUiController.php:71-73` |
| Rates UI | `GET /ui/rates` real template | `/ui/rates` shell page | **S (Laravel)** | Medium | `app.py:1322`; `ParityUiController.php:111` |
| Imports UI | `GET /ui/imports` real template | `/ui/imports` shell page | **S (Laravel)** | High | `app.py:1340`; `ParityUiController.php:78` |
| Dashboard UI | `GET /ui/dashboard` real | `GET /ui/dashboard` real dashboard view | **P parity** | Medium | `app.py:4481`; `ParityUiController.php:30-40` |
| Reports UI | `GET /ui/reports` real | `GET /ui/reports` real view | **P parity** | Medium | `app.py:1355`; `ParityUiController.php:42-50` |
| Reconciliation UI | `GET /ui/reconciliation` real | `GET /ui/reconciliation` real view | **P parity** | Medium | `app.py:1360`; `ParityUiController.php:52-60` |
| Billing run API | `POST /billing/run` active | `POST /billing/run` active | **P parity** | Critical | `app.py:3485+`; `routes/web.php` billing group + `DraftBillingFlowService.php:1207+` |
| Billing finalize API | `POST /api/billing/finalize` active | `POST /api/billing/finalize` active | **P parity** | Critical | `app.py:4579+`; `BillingDraftController.php:39-45`; `DraftBillingFlowService.php:378+` |
| Billing approve | `POST /billing/approve` -> 410 | Laravel same -> 410 | **B parity** | Medium | `app.py:3593-3596`; `DraftBillingFlowService.php:560-567` |
| Adjustments create | `POST /billing/adjustments/create` -> 410 | Laravel same -> 410 | **B parity** | Medium | `app.py:3644-3646`; `DraftBillingFlowService.php:570-577` |
| Adjustments approve | `POST /billing/adjustments/approve` -> 410 | Laravel same -> 410 | **B parity** | Medium | `app.py:3675-3678`; `DraftBillingFlowService.php:580-587` |
| Recovery payment | `POST /recovery/payment` -> 410 | Laravel same -> 410 | **B parity** | Medium | `app.py:3852-3854`; `DraftBillingFlowService.php:590-597` |
| Billing lock | Active with month-state checks | Active with month-state checks | **P parity** | Medium | `app.py:3599+`; `DraftBillingFlowService.php:529-557` |
| Reports monthly summary | `GET /reports/monthly-summary` active | Active | **P parity** | Medium | `app.py:3705+`; `BillingDraftController.php:89+` |
| Reconciliation report | Active | Active | **P parity** | Medium | `app.py:3760+`; `DraftBillingFlowService.php:601+` |
| Export reconciliation | Active | Active | **P parity** | Medium | `app.py:4107+`; `BillingDraftController.php:236+` |
| Electric V1 run | Active | Active | **F/P** | Medium | `app.py:1469`; `routes/electric_v1.php`; `ElectricV1Controller.php` |
| Electric V1 exceptions/runs | Not clearly exposed in Flask app route set | Present in Laravel (`/api/electric-v1/exceptions`, `/runs`) | **Extra Laravel** | Low | `routes/electric_v1.php`; `ElectricV1Controller.php:27-42` |

## Route-level conclusion
- Laravel has broad route coverage and some extra compatibility aliases.
- Parity gap is **not mostly route absence**; it is **module depth + schema contract + placeholder UI**.