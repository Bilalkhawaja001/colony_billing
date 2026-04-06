# MODULE_ACCEPTANCE_CHECKLISTS

## Universal checklist (applies to every module)
- [ ] Not rendered via `ui.page` generic shell
- [ ] Dedicated blade exists
- [ ] Backend route/controller/service wiring proven
- [ ] Role protection verified
- [ ] Month-lock behavior correct (if write path)
- [ ] At least 1 successful operator path proven
- [ ] At least 1 failure/guard path proven
- [ ] Feature tests added and passing
- [ ] Evidence artifact saved (test output + screenshot/log)

## P0 module-specific checklists

### Billing workspace (T-001)
- [ ] run action from page
- [ ] lock action from page
- [ ] fingerprint/report link path from page
- [ ] locked month state visible and enforced

### Month cycle (T-002)
- [ ] open month action
- [ ] transition action
- [ ] state table/list present
- [ ] invalid transition error handling

### Imports workspace (T-003)
- [ ] ingest-preview trigger
- [ ] mark-validated trigger
- [ ] error-report retrieval link
- [ ] validation feedback on page

### Rates workspace (T-004)
- [ ] rates upsert action
- [ ] rates approve action
- [ ] monthly config/history visible
- [ ] month-lock guard behavior visible