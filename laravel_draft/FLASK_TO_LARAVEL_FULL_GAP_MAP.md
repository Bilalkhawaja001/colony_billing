# FLASK_TO_LARAVEL_FULL_GAP_MAP

Module-by-module product gap map (route parity excluded as progress metric).

| Module | Flask | Laravel current | Gap type | Exact missing parity |
|---|---|---|---|---|
| Billing workspace | REAL | SHELL | UI + workflow depth gap | Full workspace UI actions, in-page run controls, operator context state |
| Month cycle governance | REAL | SHELL | UI gap | Full month lifecycle operator screen, state transitions UX parity |
| Imports | REAL | SHELL | UI gap | ingestion preview/error handling UI parity (backend exists) |
| Rates | REAL | SHELL | UI gap | rates management UI parity + approval UX visibility |
| Water meters | REAL | SHELL | UI gap | operator water module screens parity |
| Van | REAL | SHELL | UI gap | van module operator screen parity |
| Employee master | REAL | SHELL | UI/CRUD gap | forms/list/edit/delete UX parity |
| Employees | REAL | SHELL | UI/CRUD gap | search/filter/list/detail UX parity |
| Employee helper | REAL | SHELL | UI gap | helper flow parity |
| Unit master | REAL | SHELL | UI/CRUD gap | unit master operator UX parity |
| Meter master | REAL | SHELL | UI/CRUD gap | meter data operator UX parity |
| Meter ingest page | REAL | SHELL | UI workflow gap | ingest workflow page-level parity |
| Rooms | REAL | SHELL | UI/CRUD gap | rooms operator UX parity |
| Occupancy | REAL | SHELL | UI/CRUD gap | occupancy operator UX parity |
| Elec summary | REAL | SHELL | UI gap | readable summary/operator controls parity |
| Inputs mapping/hr/readings/ro | REAL | SHELL | UI gap | full input ops screens missing |
| Masters/* pages | REAL | SHELL | UI gap | grouped master ops UI missing |
| Finalized months UI | REAL | SHELL | UI gap | finalized-months ops view parity |
| Dashboard | REAL | REAL/PARTIAL | depth gap | visual/interaction depth vs Flask |
| Reports | REAL | REAL/PARTIAL | depth gap | complete filters/flows parity |
| Reconciliation | REAL | REAL/PARTIAL | depth gap | correction/drilldown parity depth |
| Family details | REAL | PARTIAL | workflow depth gap | full operator flow parity |
| Results/logs | REAL | PARTIAL | workflow depth gap | complete listing/filter/action parity |
| EV1 domain | REAL | REAL/PARTIAL | (keep untouched) | no change per lock |
| Removed 410 flows | BLOCKED | BLOCKED | no gap (policy) | keep blocked |

## Biggest blockers to full Flask-like recreation
1. Shell-heavy UI layer for major modules.
2. Backend exists for many endpoints but operator surface not rebuilt.
3. Migration sequence was endpoint-hardening-first, module-recreation-later.

## What is already aligned
- non-EV1 core limited workflow hardening + bootstrap tests
- Diff-001 persisted line mismatch closed
- intentional removed flows aligned (410)
- EV1 preserved

## What still prevents full parity
- Module/page depth parity for high-traffic operator areas remains incomplete.