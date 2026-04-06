# P3_TICKET_SEQUENCE_V1 (Frozen)

Target: End-to-end operator workflow parity + cross-module product parity (not page parity).
Constraints: strict order, EV1 untouched, no uncontrolled parallel work.

## Execution order (strict)
1. **P3-T001** Month-state governed operator chain parity (`T-002 -> T-004 -> T-003 -> T-001 -> T-015`)
2. **P3-T002** Inputs-to-compute-to-electric-summary chain parity (`T-010 -> T-001(run/compute) -> T-013`)
3. **P3-T003** Water + Van downstream impact parity into dashboard/reports/reconciliation (`T-011 + T-012 -> T-016`)
4. **P3-T004** Family/results/logs cross-link parity into report surfaces (`T-014 -> T-016`)
5. **P3-T005** Role/guard matrix hardening across P3 chains (write guards + lock-state + unauthorized role paths)
6. **P3-T006** P3 integrated rerun/idempotency + final cross-module completeness gate

## Ticket definitions

### P3-T001
- Module/workflow: Month-state governed operator chain
- Exact scope: open/transition, rates config/approve, import preview/validate/error loop, billing run/lock/finalized visibility, persistence + reload + guard proofs.
- Why in P3: Validates orchestration parity across foundational modules, not individual page reachability.
- Dependencies: T-002, T-004, T-003, T-001, T-015 completed in earlier phases.
- Upstream required: month governance + rates/import services + billing run/lock path.
- Downstream effects: creates trusted month/run state consumed by reports and later P3 tickets.
- Priority/order: 1

### P3-T002
- Module/workflow: Inputs to compute to elec summary
- Exact scope: source inputs propagate to compute outputs and elec summary/reload views with deterministic state.
- Why in P3: Cross-module data correctness parity objective.
- Dependencies: P3-T001, T-010, T-013, T-001.
- Upstream required: valid month/run context from P3-T001.
- Downstream effects: dashboard/report electric metrics correctness.
- Priority/order: 2

### P3-T003
- Module/workflow: Water/Van downstream reporting impact
- Exact scope: water and van operational changes reflected in dashboard/reports/reconciliation.
- Why in P3: product-level parity across independent modules converging in reporting.
- Dependencies: P3-T001, T-011, T-012, T-016.
- Upstream required: approved/locked month state and rate context.
- Downstream effects: reconciliation totals and management reporting.
- Priority/order: 3

### P3-T004
- Module/workflow: Family/results/logs to reporting chain
- Exact scope: family/result/log mutations appear correctly in employee/unit/report views with reload persistence.
- Why in P3: verifies cross-view consistency and auditability.
- Dependencies: P3-T001, T-014, T-016.
- Upstream required: month/run context and registry linkage.
- Downstream effects: employee-wise and unit-wise report fidelity.
- Priority/order: 4

### P3-T005
- Module/workflow: Role and guard matrix hardening
- Exact scope: role denial + locked-month write block + invalid transition checks across all prior P3 chains.
- Why in P3: guard correctness is part of operator workflow parity.
- Dependencies: P3-T001..T004.
- Upstream required: all chain endpoints operational.
- Downstream effects: safe deterministic behavior under failure/abuse paths.
- Priority/order: 5

### P3-T006
- Module/workflow: Integrated rerun/idempotency and completeness gate
- Exact scope: replay full P3 journey, ensure stable outputs/no duplication and produce final completeness evidence.
- Why in P3: final product parity proof must include rerun safety and deterministic outcomes.
- Dependencies: P3-T001..T005.
- Upstream required: all previous P3 tickets green.
- Downstream effects: unlock final completeness gate.
- Priority/order: 6
