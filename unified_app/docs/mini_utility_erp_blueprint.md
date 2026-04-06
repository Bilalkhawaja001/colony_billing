# Mini Utility-ERP with Accounting Discipline

## Objective
Build a deterministic, audit-first utility billing platform where every month is a controlled accounting period.

## Core Principles
1. Deterministic billing engine (same input => same output)
2. Month lifecycle governance (`OPEN -> DRAFT -> APPROVAL -> LOCKED`)
3. Immutable locked periods (adjustments via journals, no silent edits)
4. Full audit trail for config + transactional changes
5. Role-based control (admin-only critical actions)

## Target Architecture
- `domain/` business rules, pure deterministic calculations
- `application/` use-cases (orchestration, validation)
- `infrastructure/` sqlite repositories + adapters
- `interfaces/http/` Flask routes

## Domain Modules
- Month Ledger
- Rates Config
- Meter/Occupancy Facts
- School Van Allocation
- Billing Engine
- Reconciliation
- Audit & Approvals

## Deterministic Engine Contract
Input:
- month_cycle
- approved rates snapshot
- occupancy snapshot
- meter readings snapshot
- variable expense snapshot
- school van active list snapshot

Output:
- normalized billing lines (DECIMAL quantized)
- summary totals
- deterministic fingerprint

## Governance Controls
- Admin-only:
  - month transition to APPROVAL/LOCKED
  - rates upsert
  - rerun billing
- Maker-checker:
  - creator != approver
- Locked period controls:
  - reject mutation endpoints
  - allow adjustment journal entries only

## Audit Model
`util_audit_log`
- entity_type
- entity_id
- action
- actor_user_id
- before_json
- after_json
- correlation_id
- created_at

## Delivery Plan
### Phase-1 (now)
- Domain scaffolding + deterministic primitives
- Admin guardrails on rates module
- Monthly rates history visibility

### Phase-2
- Extract billing run to service layer
- Add fingerprint + reproducibility endpoint
- Add audit log table + write hooks

### Phase-3
- Lock-period immutability + adjustment journal
- Maker-checker approvals + role matrix
- Reconciliation dashboard

### Phase-4
- Automated tests (domain determinism + API contract)
- hardening + deployment checklist
