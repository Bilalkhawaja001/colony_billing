# NEXT_GATE_FOR_P2 (Frozen)

## 1) P1 proven scope
P1 proof confirms these modules are now non-shell and reachable through operator pages:
- Unit master (`/ui/unit-master`)
- Rooms (`/ui/rooms`)
- Occupancy (`/ui/occupancy`)
- Meter master/readings (`/ui/meter-master`)
- Employee master/helper (`/ui/employee-master`, `/ui/employee-helper`)
- Inputs pages (`/ui/inputs/mapping`, `/ui/inputs/hr`, `/ui/inputs/readings`, `/ui/inputs/ro`)

Proof base:
- `P1_EXECUTION_REPORT.md`
- PHPUnit run: `P1ExecutionOrderTest|P0WorkspaceUiTest` => 10/10 pass

## 2) P1 limits (important)
P1 closure means “non-shell + reachable actions”, not full Flask operator-depth parity yet.
Current limits still open:
- advanced filtering/grids parity not fully proven
- edit/delete correction loops depth not fully proven per module
- cross-module operational journey proof (operator daily cycle) still incomplete
- UX/state messaging parity (all edge/error paths) not fully benchmarked vs Flask

## 3) Deeper acceptance criteria required for operational parity
Before marking any P2 ticket as REAL parity closure, require:
1. Full CRUD/action lifecycle (create + list + update + delete where Flask supports)
2. Guarded-failure behavior parity (role, month-lock, validation, conflict)
3. Multi-step operator flow proof from UI (not only direct API calls)
4. Data persistence verification in target tables after each action
5. Export/report linkage proof where module feeds reporting
6. Regression tests per module covering success + failure + rerun/idempotency where applicable

## 4) Exact P2 order
1. T-011 Water module pages
2. T-012 Van module page
3. T-013 Elec summary page
4. T-014 Family details/results/logs depth
5. T-015 Finalized months UI
6. T-016 Dashboard/reports/reconciliation depth closure

## 5) Blockers / dependencies for P2
- T-011 depends on occupancy/mapping quality from P1 outputs.
- T-012 depends on rates and monthly variable expense visibility.
- T-013 depends on billing/report data correctness surfaced in P0/P1.
- T-014 depends on family/employee linkage consistency.
- T-015 depends on month-cycle transitions/finalize history integrity.
- T-016 depends on outputs from T-011/T-012/T-013 to avoid shallow dashboard/report closure.

## Gate decision
P2 coding may start only after this file freeze acknowledgement (done).