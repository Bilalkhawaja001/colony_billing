# CSV_FEATURE_IMPLEMENTATION_PLAN

## Objective
Deliver operator-usable CSV import/template capabilities on priority pages with minimal localized changes.

## Strategy
- Prefer UI-layer CSV handling (FileReader + row-wise POST to existing endpoints)
- No schema changes
- No backend refactor
- Reuse existing write endpoints:
  - `/meter-reading/upsert`
  - `/meter-unit/upsert`
  - `/units/upsert`
  - `/employees/import`

## Implemented

### Meter Master
- Added download templates:
  - `meter_readings_template.csv`
  - `meter_mappings_template.csv`
- Added CSV upload for readings and mappings
- Added line-level error aggregation + success summary
- Added mapping reload/filter table

### Unit Master
- Added `unit_master_template.csv` download
- Added CSV upload (row-wise `/units/upsert`)
- Added list table reload (`/units`)

### Inputs HR
- Added `inputs_hr_template.csv` download
- Added CSV upload to `/employees/import` (csv_text)
- Added employee list verification table (`/employees`)

### Employee Master
- Full CSV import already wired; workflow kept and organized
- UI now clearly exposes import action + feedback in operator flow

## Validation standard
- Success: per-page bulk summary (`processed/ok/fail`)
- Error: row/line and API response in result panel
- Role/access: enforced by existing route middleware
