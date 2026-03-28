# APP_DISCOVERY_AND_COMPONENT_MAP

## 1) App roots
- **Flask root:** `C:\Users\Bilal\clawd\mbs_project\unified_app`
  - Runtime app entry: `unified_app/api/app.py`
- **Laravel root:** `C:\Users\Bilal\clawd\mbs_project\laravel_draft`
  - Runtime routing: `laravel_draft/routes/web.php`, `routes/electric_v1.php`

## 2) Billing-relevant component inventory

### Flask (reference)
- API + orchestration
  - `api/app.py` (monolith routes + auth + billing + reports + exports + month/rates)
  - `api/domain/billing_engine.py`, `electricity_compute.py`, `month_guard.py`
  - `api/electric_v1/orchestration_service.py`, `read_service.py`, domain/repositories
- UI templates
  - `templates/billing.html`, `dashboard_futuristic.html`, `reconciliation.html`, `reports.html`, `month.html`, `rates.html`, `imports.html`, `elec_summary.html`, `electric_v1_*`
- DB contracts
  - `api/migrations/20260322_create_electric_v1_tables.sql`
  - Additional runtime DDL in `api/app.py` (`CREATE TABLE IF NOT EXISTS ...` blocks)
- Tests
  - `tests/test_electric_v1_*`
  - `proof/test_colony_billing_engine.py`, `proof/test_mbs0*.py`, reconciliation/recovery/governance tests

### Laravel (target)
- Routing + controllers
  - `routes/web.php`, `routes/electric_v1.php`
  - `app/Http/Controllers/Billing/*`, `Ui/ParityUiController.php`, `ElectricV1/ElectricV1Controller.php`
- Services / repositories
  - `app/Services/Billing/DraftBillingFlowService.php`
  - `app/Services/ElectricV1/*` + `app/Repositories/ElectricV1/*`
  - `app/Services/Month/MonthStateService.php`
- Middleware / auth guards
  - `app/Http/Middleware/EnsureAuthenticated.php`
  - `ForcePasswordChange.php`, `RoleGate.php`, `ShellPathRbac.php`, `MonthGuardShell.php`
- Views
  - Generic shells: `resources/views/ui/page.blade.php`
  - Real-ish: `ui/dashboard.blade.php`, `ui/reports.blade.php`, `ui/reconciliation.blade.php`, plus family/results/logs
- DB contracts
  - `database/migrations/*.php` (auth, electric_v1 core, units/rooms/employees/water/family/meter tables)
- Tests
  - `tests/Feature/*` incl. billing, month guard, reports, parity, electric_v1 role/workflow/rerun tests

## 3) High-level architecture map

### Flask
`UI templates -> app.py routes -> SQL helper q()/exec_txn() -> SQLite tables`

Plus parallel deterministic EV1 stack:
`/api/electric-v1/* -> electric_v1/orchestration_service.py -> repos -> EV1 tables`

### Laravel
`routes/web.php + routes/electric_v1.php -> controllers -> services/repositories -> SQLite`

Observed split:
- **Many UI routes -> generic shell view** (`ParityUiController::renderUiPage`) 
- **Billing core in one draft service** (`DraftBillingFlowService`) with mixed parity + intentional 410 endpoints
- **EV1 has dedicated domainized services** (`app/Services/ElectricV1/*`) and looks structurally mature