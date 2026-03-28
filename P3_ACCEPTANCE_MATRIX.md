# P3_ACCEPTANCE_MATRIX (Frozen)

## Universal P3 acceptance requirements (all tickets)
1. End-to-end workflow works
2. Cross-module dependencies work
3. Saved data affects downstream modules correctly
4. Validation and error states work
5. Import/export/report side-effects work where relevant
6. Role/access behavior is correct
7. Reload/state persistence is correct
8. Deterministic test-backed evidence
9. Rollback-safe note if contract/query changes

---

## P3-T001 Acceptance Matrix
- Ticket ID: P3-T001
- Scope: `T-002 -> T-004 -> T-003 -> T-001 -> T-015`
- Acceptance criteria:
  - Open + valid transitions succeed
  - Invalid transition path rejected
  - Rates config/upsert + approve persisted
  - Import preview token + mark validated + error report retrieval proven
  - Billing run created + lock successful under APPROVAL state
  - Finalized months visibility proven after lock-state path
  - Locked-month write path blocked
  - Role denial proven on write path
  - Reload persistence proven across touched modules
- Test/proof required:
  - Deterministic feature test covering full chain and failure paths
  - DB assertions after each stage
  - proof log with command output + pass/fail summary
- Rollback note if contract/query changes:
  - Any SQL/contract change must include exact old/new query, affected endpoints, and revert patch snippet.

## P3-T002 Acceptance Matrix
- Ticket ID: P3-T002
- Scope: `T-010 -> T-001(run/compute) -> T-013`
- Acceptance criteria:
  - Input writes persist and are consumed by compute
  - Elec summary reflects compute outputs and survives reload
  - Validation/role/lock failures proven
- Test/proof required: chain feature test + DB checkpoints + deterministic rerun check.
- Rollback note: required for any query/contract shift.

## P3-T003 Acceptance Matrix
- Ticket ID: P3-T003
- Scope: `T-011 + T-012 -> T-016`
- Acceptance criteria:
  - Water/van operational writes update downstream dashboard/reports/reconciliation aggregates
  - export/report side-effects proven
  - reload + role/guard failures proven
- Test/proof required: integration feature test with downstream totals assertions.
- Rollback note: required for any query/contract shift.

## P3-T004 Acceptance Matrix
- Ticket ID: P3-T004
- Scope: `T-014 -> T-016`
- Acceptance criteria:
  - Family/details/results/log updates flow into report surfaces accurately
  - validation + role + reload persistence proven
- Test/proof required: feature test with before/after report deltas.
- Rollback note: required for any query/contract shift.

## P3-T005 Acceptance Matrix
- Ticket ID: P3-T005
- Scope: guard/role matrix over P3 chains
- Acceptance criteria:
  - unauthorized role denials proven on all relevant write paths
  - locked month write blocks proven
  - invalid transition and malformed payload failures proven
- Test/proof required: negative-path matrix test pack.
- Rollback note: required for any query/contract shift.

## P3-T006 Acceptance Matrix
- Ticket ID: P3-T006
- Scope: integrated rerun/idempotency + final gate
- Acceptance criteria:
  - full P3 journey replay produces stable deterministic outputs
  - no duplicate or drifting records in key downstream tables
  - final completeness gate generated and frozen
- Test/proof required: end-to-end rerun suite + rowcount/checksum evidence.
- Rollback note: required for any query/contract shift.
