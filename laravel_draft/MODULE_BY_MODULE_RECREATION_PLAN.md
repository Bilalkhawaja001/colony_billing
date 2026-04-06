# MODULE_BY_MODULE_RECREATION_PLAN

Goal: Full Flask product recreation in Laravel (module depth), not route parity.

## Freeze rules
- EV1 untouched.
- No broad refactor.
- Work module-by-module with acceptance evidence.
- Route existing ≠ done.

## Build order (recommended)

### Wave 1 (P0 operator core)
1. **Billing workspace (`/ui/billing`)**
   - Replace shell with dedicated blade + controller composition.
   - Wire real actions: run/fingerprint/lock/report links + state feedback.
   - Acceptance: operator can execute core month billing lifecycle from page.

2. **Month cycle (`/ui/month-cycle`)**
   - Replace shell.
   - Wire open/transition controls + state table + lock warnings.
   - Acceptance: governance actions visible + guarded + auditable.

3. **Imports (`/ui/imports`, `/ui/meter-register-ingest`)**
   - Replace shells with real ingestion pages.
   - Wire preview/validation/error-report loop.
   - Acceptance: end-to-end import path executable from UI.

4. **Rates (`/ui/rates`)**
   - Replace shell.
   - Wire upsert/approve/month-history config flows.
   - Acceptance: monthly rates lifecycle operable from UI.

### Wave 2 (P1 master/input ops)
5. Employee master + employees + helper pages
6. Unit master
7. Rooms
8. Occupancy
9. Meter master
10. Inputs mapping/hr/readings/ro pages

Acceptance for each module:
- list/create/update/delete or applicable operator actions
- role-guarded behavior
- month-guard respect for write actions
- deterministic response/error handling

### Wave 3 (P1 reporting/reconciliation depth)
11. Reports page depth (filters, drilldown links, parity columns)
12. Reconciliation page depth (actions + export hooks + issue visibility)
13. Family details/results/logs parity depth

### Wave 4 (P2 polish + closure)
14. Finalized months UI
15. Dashboard UX parity refinements
16. Remove obsolete shell routes or keep explicit “not in scope” labels

## Per-module implementation checklist
- [ ] Flask source module behavior map extracted (routes + template actions + DB effects)
- [ ] Laravel dedicated page created (if shell)
- [ ] Controller/service wiring complete
- [ ] Validation + role gates proven
- [ ] Regression tests added (UI + API contract)
- [ ] Evidence snapshot recorded

## Success gates
- Each module marked REAL only after workflow proof.
- No module considered complete on route/page render only.
- Final objective: Laravel module truth map has no critical SHELL in Flask-real modules.