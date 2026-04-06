# P0_IMPLEMENTATION_ORDER (Freeze v1)

Execution order:
1. **T-002 Month cycle governance**
   - reason: month state drives billing/rates/import UX behavior.
2. **T-004 Rates workspace**
   - reason: pricing controls are hard dependency for billing workspace.
3. **T-003 Imports workspace**
   - reason: input readiness path before billing execution.
4. **T-001 Billing workspace**
   - reason: central operator module; depends on rates/month/import visibility.

P0 gate to move into P1:
- all 4 modules are non-shell
- each has at least one end-to-end UI-driven workflow proof
- role and month-lock behavior proven in feature tests