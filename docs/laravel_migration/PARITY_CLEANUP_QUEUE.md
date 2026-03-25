# PARITY_CLEANUP_QUEUE

## P0 Cleanup (before production cutover)
1. Install/verify PHP + Composer runtime on validation host.
2. Run full runtime sequence:
   - `composer install`
   - `.env` setup + `php artisan key:generate`
   - sqlite db create + `php artisan migrate`
   - `php artisan test`
3. Replace `/export/excel/reconciliation` CSV adapter with true XLSX parity.
4. Implement proven active export siblings if required:
   - `/export/excel/monthly-summary`
   - `/export/pdf/monthly-summary`
5. Add fixture-based parity snapshots (Flask vs Laravel):
   - reconciliation summary/by_utility/by_employee
   - monthly summary
   - employee bill summary
6. Move month guard from shell/session config to domain month-state service.

## P1 Cleanup
- audit trail parity for lock/finalize/report/recovery-adjustment 410 endpoints
- response text exact-match pass
- CRLF/LF normalization and style pass

## Out of Scope (separate module)
- electric_v1 route/service parity
