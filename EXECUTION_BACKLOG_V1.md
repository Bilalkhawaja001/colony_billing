# EXECUTION_BACKLOG_V1 (Freeze v1)

Source artifacts frozen:
1. `FLASK_COMPLETE_MODULE_SOURCE_MAP.md`
2. `LARAVEL_CURRENT_MODULE_TRUTH_MAP.md`
3. `FLASK_TO_LARAVEL_FULL_GAP_MAP.md`
4. `MODULE_BY_MODULE_RECREATION_PLAN.md`

## Ticket template fields
- Ticket ID
- Module
- Current Laravel status
- Flask source reference
- Exact gap
- Priority
- Dependencies
- Exact files likely to change
- Acceptance criteria
- Test/proof required
- Definition of done

---

## T-001
- Module: Billing workspace (`/ui/billing`)
- Current Laravel status: SHELL
- Flask source reference: `unified_app/templates/billing.html`, `unified_app/api/app.py` (`/billing/*`)
- Exact gap: Dedicated operator workspace missing; only shell page exists.
- Priority: P0
- Dependencies: T-002, T-003, T-004 APIs available for UI wiring
- Exact files likely to change:
  - `laravel_draft/app/Http/Controllers/Ui/ParityUiController.php`
  - new `laravel_draft/resources/views/ui/billing.blade.php`
  - optional dedicated view-model service in `app/Services/Billing/*`
- Acceptance criteria:
  - Real billing workspace page (not generic shell)
  - Run/lock/fingerprint/report action controls visible and role-gated
  - Locked-month behavior reflected in UI state
- Test/proof required:
  - Feature test: page render + action triggers + locked-month block messaging
  - screenshot/proof snapshot
- Definition of done: Module status moves to REAL/PARTIAL with executable workflow from page.

## T-002
- Module: Month cycle governance (`/ui/month-cycle`)
- Current Laravel status: SHELL
- Flask source reference: `templates/month.html`, `/month/open`, `/month/transition`
- Exact gap: Governance operator screen absent.
- Priority: P0
- Dependencies: month services + guard middleware (already present)
- Files likely to change:
  - `ParityUiController.php`
  - new `resources/views/ui/month-cycle.blade.php`
- Acceptance criteria: open/transition state table, role and state errors surfaced.
- Test/proof: feature tests for OPEN->APPROVAL->LOCKED transitions from UI path.
- DoD: not shell; real governance screen.

## T-003
- Module: Imports workspace (`/ui/imports`, `/ui/meter-register-ingest`)
- Current status: SHELL
- Flask source: `templates/imports.html`, `meter_register_ingest.html`, `/imports/*`
- Exact gap: Ingest-preview/error-report operator UI missing.
- Priority: P0
- Dependencies: import endpoints stable
- Files likely:
  - `ParityUiController.php`
  - new `resources/views/ui/imports.blade.php`
  - new `resources/views/ui/meter-register-ingest.blade.php`
- Acceptance: preview/validate/error-report loop executable from UI.
- Test/proof: feature tests for ingest preview + error report token page link.
- DoD: end-to-end import flow from page.

## T-004
- Module: Rates workspace (`/ui/rates`)
- Current status: SHELL
- Flask source: `templates/rates.html`, `/rates/*`, `/monthly-rates/*`
- Exact gap: No real rates lifecycle UI.
- Priority: P0
- Dependencies: rates services + month state
- Files likely:
  - `ParityUiController.php`
  - new `resources/views/ui/rates.blade.php`
- Acceptance: upsert/approve/history/config actions + lock-state behavior.
- Test/proof: feature tests for rates UI actions + month guard constraints.
- DoD: operator can manage monthly rates from UI.

## T-005
- Module: Employee master + employees + helper
- Current status: SHELL
- Flask source: `employee_master.html`, `employees.html`, `employee_helper.html`, `/employees*`, `/registry/employees/*`
- Exact gap: CRUD/search/import/promote UX depth missing.
- Priority: P1
- Dependencies: T-003 for import cohesion
- Files likely: controller UI methods + three blade pages + optional JS fetch wiring.
- Acceptance: list/search/detail/create/update/delete + import preview/commit links.
- Test/proof: feature tests covering CRUD + import hooks.
- DoD: no shell pages in employee module cluster.

## T-006
- Module: Unit master
- Current status: SHELL
- Flask source: `unit_master.html`, `/units*`, `/api/units/reference*`
- Gap: real unit management UI absent.
- Priority: P1
- Dependencies: none hard
- Files likely: new `ui/unit-master.blade.php`, controller mapping.
- Acceptance: list/upsert/delete + reference/resolve UX parity.
- Test/proof: feature tests for upsert/delete flows.
- DoD: real operator unit page.

## T-007
- Module: Room master
- Current status: SHELL
- Flask source: `rooms.html`, `/rooms*`
- Gap: room CRUD screen absent.
- Priority: P1
- Dependencies: T-006 (unit context)
- Files likely: `ui/rooms.blade.php`, controller map.
- Acceptance: list/upsert/delete with unit-aware validation.
- Test/proof: feature tests for room CRUD.
- DoD: real room module.

## T-008
- Module: Occupancy
- Current status: SHELL
- Flask source: `occupancy.html`, `/occupancy*`, `/api/occupancy/autofill`
- Gap: occupancy operator screen missing.
- Priority: P1
- Dependencies: T-006, T-007
- Files likely: `ui/occupancy.blade.php`, controller map.
- Acceptance: context fetch + upsert/delete + autofill action.
- Test/proof: feature tests for occupancy workflows.
- DoD: real occupancy module.

## T-009
- Module: Meter master + readings + meter-unit
- Current status: SHELL
- Flask source: `meter_master.html`, `/meter-reading/*`, `/meter-unit*`
- Gap: full meter operator workflows missing.
- Priority: P1
- Dependencies: T-006
- Files likely: `ui/meter-master.blade.php` + wiring.
- Acceptance: latest reading fetch + upsert reading + meter-unit mapping.
- Test/proof: feature tests for reading upsert path.
- DoD: meter module real.

## T-010
- Module: Inputs Mapping / HR / Readings / RO pages
- Current status: SHELL
- Flask source: `/ui/inputs/*` + finalize dependencies
- Gap: source input surfaces absent as real pages.
- Priority: P1
- Dependencies: T-003, T-009
- Files likely: four dedicated blades + controller methods.
- Acceptance: input summaries and action links available.
- Test/proof: render + dependency action tests.
- DoD: no shell for input surfaces.

## T-011
- Module: Water module pages
- Current status: SHELL
- Flask source: `water_meters.html`, water APIs
- Gap: water analytics/operator page missing.
- Priority: P1
- Dependencies: T-008
- Files likely: `ui/water-meters.blade.php`
- Acceptance: occupancy snapshot/zone adjustments/allocation preview integrated.
- Test/proof: feature test for water module action links.
- DoD: real water module surface.

## T-012
- Module: Van module page
- Current status: SHELL
- Flask source: `van.html`, reports/expenses hooks
- Gap: operator van page missing.
- Priority: P1
- Dependencies: T-004
- Files likely: `ui/van.blade.php`
- Acceptance: van data and related actions exposed.
- Test/proof: render + report linkage test.
- DoD: real van module.

## T-013
- Module: Elec summary page
- Current status: SHELL
- Flask source: `elec_summary.html`, report endpoints
- Gap: summary page absent as real UI.
- Priority: P2
- Dependencies: T-001
- Files likely: `ui/elec-summary.blade.php`
- Acceptance: summary table/filters linked to report APIs.
- Test/proof: render + data fetch test.
- DoD: non-shell summary page.

## T-014
- Module: Family details/results/logs depth
- Current status: PARTIAL
- Flask source: `family_details.html`, results/log templates
- Gap: page depth/controls incomplete.
- Priority: P2
- Dependencies: none hard
- Files likely: existing blades + controllers.
- Acceptance: filters/actions match Flask operator depth.
- Test/proof: feature tests for context/list/update flows.
- DoD: upgraded from PARTIAL to REAL.

## T-015
- Module: Finalized months UI
- Current status: SHELL
- Flask source: `/ui/finalized-months`
- Gap: real finalized-months operator view missing.
- Priority: P2
- Dependencies: T-002
- Files likely: `ui/finalized-months.blade.php`
- Acceptance: finalized month listing and links functional.
- Test/proof: render + list response test.
- DoD: real finalized-months module.

## T-016
- Module: Dashboard/reports/reconciliation UX depth closure
- Current status: PARTIAL
- Flask source: dashboard/reports/reconciliation templates
- Gap: interaction depth below Flask-level completeness.
- Priority: P2
- Dependencies: T-001, T-011, T-012
- Files likely: `ui/dashboard.blade.php`, `ui/reports.blade.php`, `ui/reconciliation.blade.php`
- Acceptance: filter/action/drilldown parity depth.
- Test/proof: UX flow tests + proof snapshots.
- DoD: pages marked REAL, not partial.
