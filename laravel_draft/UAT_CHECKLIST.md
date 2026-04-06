# Controlled UAT Checklist

## 1) ElectricV1 flows
- [ ] Open `/ui/electric-v1-run` as BILLING_ADMIN; page loads
- [ ] Submit run for a valid cycle via UI/API; verify success payload includes `run_id`
- [ ] Open `/ui/electric-v1-outputs`; verify outputs render for same cycle
- [ ] Verify `/api/electric-v1/outputs`, `/api/electric-v1/exceptions`, `/api/electric-v1/runs` return 200 for VIEWER
- [ ] Re-run same cycle; verify replace semantics and run history increment

## 2) Month/Billing/Reports/Imports pages
- [ ] `/ui/month-cycle` renders
- [ ] `/ui/billing` renders and billing actions reachable
- [ ] `/ui/reports` and `/ui/reconciliation` render expected summaries
- [ ] `/ui/imports` and `/ui/monthly-setup` render and APIs respond
- [ ] `/monthly-rates/config`, `/monthly-rates/history`, `/monthly-rates/config/upsert` behave as expected

## 3) Admin / Settings / Master data
- [ ] `/ui/admin/users` renders for SUPER_ADMIN
- [ ] Admin APIs: create/update/reset-password work with role checks
- [ ] Master endpoints: employees/units/rooms/occupancy/meter unit + reading paths respond correctly

## 4) Exports + print
- [ ] `/export/excel/reconciliation` downloads file
- [ ] `/export/excel/monthly-summary` downloads file
- [ ] `/export/pdf/monthly-summary` downloads PDF
- [ ] `/billing/print/{month_cycle}/{employee_id}` returns expected bill payload/print data

## 5) Approved exception awareness (intentional 410)
- [ ] Confirm `POST /billing/approve` => 410
- [ ] Confirm `POST /billing/adjustments/create` => 410
- [ ] Confirm `POST /billing/adjustments/approve` => 410
- [ ] Confirm `POST /recovery/payment` => 410

## 6) Smoke verification commands
```bash
php vendor/bin/phpunit --filter ElectricV1
php vendor/bin/phpunit
```
