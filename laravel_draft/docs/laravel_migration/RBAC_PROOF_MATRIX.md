# RBAC_PROOF_MATRIX

Scoped P0 routes use combined RBAC gates from:
- `enforce_auth_and_rbac` (global before_request)
- `_role_allowed` path permission map
- hard guards (SUPER_ADMIN areas, VIEWER write block)
- handler-level guards (`require_admin_from_request` where present)

## Status
- Per-route role truth table for scoped P0 routes: **Closed**
- Confidence: High for scoped routes covered in route map.

## Roles
- SUPER_ADMIN
- BILLING_ADMIN
- DATA_ENTRY
- VIEWER

## Notes
- This matrix is scoped to P0 verified routes only.
- Non-scoped domain routes remain out-of-scope for this batch.
