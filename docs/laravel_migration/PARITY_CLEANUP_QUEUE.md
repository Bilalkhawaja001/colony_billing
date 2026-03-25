# PARITY_CLEANUP_QUEUE

## P0 Cleanup (before production cutover)
1. Replace `/export/excel/reconciliation` CSV adapter with true XLSX writer parity.
2. Implement proven active export siblings if required by acceptance:
   - `/export/excel/monthly-summary`
   - `/export/pdf/monthly-summary`
3. Add fixture-based parity snapshots (Flask vs Laravel) for:
   - reconciliation summary/by_utility/by_employee
   - monthly summary
   - employee bill summary
4. Move month guard from shell/session config to domain month-state service.
5. Add audit trail parity for billing lock/finalize and recovery/adjustment 410 endpoints.

## P1 Cleanup
- response localization/error text exact-match pass
- CRLF/LF normalization and style pass

## Out of Scope (separate module)
- electric_v1 route/service parity
