# PHASE3_FUNCTIONAL_GAP_CLOSURE

## Scope completed
Closed remaining prioritized functional depth gaps for:
- `/ui/rooms`
- `/ui/occupancy`
- `/ui/inputs-mapping`
- `/ui/inputs-readings`
- `/ui/inputs-ro`
- `/ui/water-meters` (operational depth improvement)

## Gap closure summary

### /ui/rooms
- Added CSV template download
- Added CSV upload (row-wise existing `/rooms/upsert`)
- Added list/reload with month/unit filters
- Added row delete action
- Added result/error console with bulk line failure reporting

### /ui/occupancy
- Added CSV template download
- Added CSV upload (row-wise existing `/occupancy/upsert`)
- Added month autofill trigger (`/api/occupancy/autofill`)
- Added list/reload with filters and row delete
- Added result/error console with bulk line failure reporting

### /ui/inputs-mapping
- Upgraded from context-only page to actionable operator console:
  - rooms cascade action (`/api/rooms/cascade`)
  - occupancy context check (`/occupancy/context`)

### /ui/inputs-readings
- Upgraded to actionable page:
  - latest reading lookup (`/meter-reading/latest/{unit}`)
  - quick reading upsert (`/meter-reading/upsert`)

### /ui/inputs-ro
- Upgraded to actionable page:
  - allocation preview load (`/api/water/allocation-preview`)
  - zone adjustment upsert (`/api/water/zone-adjustments`)

### /ui/water-meters
- Deepened operational controls:
  - typed zone selector
  - one-zone upsert
  - 4-zone preset payload action
  - consolidated snapshot/adjustment/allocation load

## Guardrails
- EV1 untouched ✅
- Existing route/workflow contracts preserved ✅
- No schema changes ✅
- No broad backend refactor ✅
