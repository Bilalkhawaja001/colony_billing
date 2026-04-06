# Unified Merge Batch Summary

Merged into one app:
- Batch A core APIs
- Batch C reports + exports

Single process / single port app file:
- api/app.py

Run with:
- MBS_UNIFIED_PORT
- MBS_DB_PATH

No business rule changes.

UI/UX completion add-ons:
- Enhanced `/ui/billing` into operator dashboard flow
- Added adjustment lifecycle UI (create, approve, list)
- Added billing fingerprint action in UI
- Improved navigation consistency in sidebar
- Added reusable chip/stack UI atoms in `static/css/ui.css`
