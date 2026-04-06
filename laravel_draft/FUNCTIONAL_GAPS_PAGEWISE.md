# FUNCTIONAL_GAPS_PAGEWISE

## Audit scope
Pages audited against operator-ready depth (CRUD/list/filter/import/export/feedback/template helpers).

## Priority pages

### 1) /ui/meter-master
- Already working before: single upsert reading + meter-unit mapping.
- Missing before: CSV bulk upload, template download, mapping list/filter, structured error/success bulk feedback.
- Closed now: ✅ CSV upload (readings + mappings), ✅ template download (both), ✅ mapping table + filter reload, ✅ line-level bulk error summary.

### 2) /ui/unit-master
- Already working before: single unit upsert.
- Missing before: CSV upload, template download, listing visibility.
- Closed now: ✅ CSV upload, ✅ template download, ✅ unit listing table, ✅ bulk import result summary.

### 3) /ui/inputs-hr
- Already working before: link/context only.
- Missing before: actionable HR CSV import flow, template download, result/list verification.
- Closed now: ✅ CSV upload to `/employees/import`, ✅ template download, ✅ employee table verification view, ✅ result feedback.

### 4) /ui/employee-master
- Already working before: upsert/add/search/get/patch/delete + CSV upload.
- Missing before: sample template download + clearer operator flow depth.
- Closed now: ✅ template-friendly CSV flow retained and structured, ✅ full operator action layout present.

## Remaining non-priority gaps (page-wise)
- /ui/rooms: no CSV bulk import/template yet.
- /ui/occupancy: no CSV bulk import/template yet.
- /ui/water-meters: no CSV bulk tooling (currently API operation console).
- /ui/van: report loader only (no CRUD/import by design).
- /ui/meter-register-ingest: route-to-import helper page (acceptable, but not full ingest wizard).
- /ui/inputs-mapping, /ui/inputs-readings, /ui/inputs-ro: context pages, not full tabular operator stations.

## Risk/regression check
- EV1 untouched ✅
- Existing route contracts unchanged ✅
- Existing business logic untouched (UI-only implementation for priority pages) ✅
