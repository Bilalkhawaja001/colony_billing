# VIEW_PARITY_DIFF

## Observation
Flask reference in this audit scope (batchA + batchC) is API-first and does not carry full operator templates. Therefore UI parity is measured as:
- expected operational usability from legacy Flask experience (forms/tables/workflows)
- current Laravel blade usefulness + binding depth

## Page parity matrix

| Functional Page | Laravel Blade | Controls Present | Real Data Binding | Actionability | Placeholder Risk | Parity Result |
|---|---|---|---|---|---|---|
| Dashboard | `resources/views/auth/dashboard.blade.php` | Basic cards/links | Session-only + static cards | Navigation only | Medium | Partial |
| Billing workspace | `resources/views/auth/billing.blade.php` | Forms/buttons for APIs | No operational grids, no run history table | Limited | High | Partial |
| Month cycle | `resources/views/auth/month-cycle.blade.php` | Buttons + checklist | No live cycle timeline/state board | Limited | High | Partial |
| Reports center | `resources/views/auth/reports.blade.php` | Link hub | No in-page filters/results | Limited | Medium | Partial |
| Reconciliation | `resources/views/auth/reconciliation.blade.php` | Link actions + static checklist | No reconciliation table in UI | Limited | High | Partial |
| Profile/password change | `resources/views/profile/index.blade.php` | Form present | API bound | Actionable | Low | Near parity (unverified vs Flask auth) |
| Login/forgot/reset | `resources/views/auth/*.blade.php` | Forms present | Backend bound | Actionable | Low | Needs verification |

## Key UI gaps
1. No full billing operations grid (run list, status badges, filters, drilldowns).
2. No month-cycle state machine visualization/history.
3. No in-page report rendering/filters; mostly endpoint launchers.
4. Reconciliation UI lacks core data tables and discrepancy workflows.
5. No admin user management implementation despite route presence (`/ui/admin/users` still `auth.protected-shell`).

## Pages that appear complete but are functionally incomplete
- `/ui/billing`
- `/ui/month-cycle`
- `/ui/reports`
- `/ui/reconciliation`

## Severity
- Critical UI gap count: 1
- High UI gap count: 4
- Medium UI gap count: 2
- Low UI gap count: 0
