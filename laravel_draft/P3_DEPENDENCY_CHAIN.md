# P3_DEPENDENCY_CHAIN (Frozen)

## P3 dependency graph (strict)
P3-T001 -> P3-T002 -> P3-T003 -> P3-T004 -> P3-T005 -> P3-T006

## Ticket-level dependencies

### P3-T001
- Depends on: T-002, T-004, T-003, T-001, T-015 (already complete)
- Hard blockers:
  1. Month transition correctness (invalid/valid state handling)
  2. Billing lock precondition (month must be APPROVAL, run must be APPROVED)
  3. Month guard lock behavior on write endpoints

### P3-T002
- Depends on: P3-T001 + T-010 + T-013 + T-001
- Hard blockers:
  1. Input data contract compatibility with compute
  2. Elec summary query contract consistency

### P3-T003
- Depends on: P3-T001 + T-011 + T-012 + T-016
- Hard blockers:
  1. Rates/month context consistency
  2. Downstream report aggregate alignment

### P3-T004
- Depends on: P3-T001 + T-014 + T-016
- Hard blockers:
  1. Family-registry linkage integrity
  2. Report surface synchronization

### P3-T005
- Depends on: P3-T001..P3-T004
- Hard blockers:
  1. Complete write-endpoint inventory for role/guard checks
  2. Stable lock-state enforcement semantics

### P3-T006
- Depends on: P3-T001..P3-T005
- Hard blockers:
  1. Deterministic rerun behavior
  2. No-duplication guarantees on key persisted outputs

## Upstream -> downstream effects map
- Month governance state drives rates/import/billing action availability.
- Billing run/lock state gates finalized/reporting surfaces.
- Inputs/water/van/family mutations propagate into dashboard/reports/reconciliation.
- Guard/role correctness ensures parity under failure and unauthorized usage.
