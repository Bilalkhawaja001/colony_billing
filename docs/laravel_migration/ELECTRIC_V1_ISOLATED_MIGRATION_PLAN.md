# ELECTRIC_V1_ISOLATED_MIGRATION_PLAN

## Objective
Migrate `electric_v1` as an isolated module without modifying already-GO core behavior.

## Principles
- Keep module boundary strict (no hidden coupling into core billing flows).
- Route/module namespace isolation.
- Independent test matrix and rollout controls.
- Feature flag controlled activation.

## Phase Plan

### Phase 1: Discovery Freeze
- Inventory all `electric_v1` routes/functions/tables from Flask evidence.
- Confirm active vs legacy paths.
- Lock evidence map before coding.

### Phase 2: Module Skeleton
- Create isolated Laravel module namespace (e.g., `App\Modules\ElectricV1`).
- Add dedicated routes file and middleware group.
- Add module-specific request/service/model layers.

### Phase 3: Data Layer Parity
- Map and migrate electric_v1 tables only.
- Keep migrations isolated from core billing migrations.
- Add read/write contracts and fixture data for parity tests.

### Phase 4: Workflow Port
- Port run orchestration + output endpoints.
- Preserve guard semantics and deterministic behavior.
- Explicitly block unproven branches with TODO markers.

### Phase 5: Validation
- Module-only feature tests.
- Snapshot parity checks vs Flask outputs.
- Performance sanity checks for batch runs.

### Phase 6: Controlled Rollout
- Enable behind feature flag per environment.
- Pilot with limited operator set.
- Observe logs/metrics; then expand.

## Deliverables
- electric_v1 route parity map
- module schema plan + migrations
- module test parity report
- rollout + rollback note for module flag

## Out-of-Scope
- Any change to core GO billing/auth/month-guard behavior unless a bug is proven.
