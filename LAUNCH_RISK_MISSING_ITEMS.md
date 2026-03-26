# LAUNCH_RISK_MISSING_ITEMS

## Launch risk summary

### Critical (launch-blocking)
- **No open critical items after this batch.**

### High
1. **Month state workflow partial parity** (`/month/open`, `/month/transition`).
2. **Import validation endpoint missing** (`/imports/mark-validated`).
3. **Missing monthly summary exports** (CSV/PDF).
4. **Operator UI function depth insufficient** (pages exist, workflow depth low).
5. **Auth parity not proven against production Flask auth code.**

### Medium
10. Lock workflow changed with stricter guards (likely intentional, needs sign-off).
11. Reconciliation is custom/net-new and requires acceptance criteria proof.
12. Recovery/adjustment disabled routes require migration communication.

## What can fail at go-live
- Existing automation hitting Flask paths fails immediately (404/410/contract mismatch).
- Month/rates operations cannot complete through parity API set.
- Report packs depending on CSV/PDF monthly-summary break.
- Users can access UI pages but cannot execute full operational cycle end-to-end.

## Recommended launch gate checklist
- [ ] Contract compatibility map published (`Flask -> Laravel` endpoint migration)
- [ ] Billing run/approve parity decision signed (restore vs deprecate)
- [ ] Rates and import lifecycle endpoints implemented or replaced with adapters
- [ ] Export parity (CSV/PDF monthly summary) restored
- [ ] Golden dataset comparison for billing totals between Flask and Laravel
- [ ] Role + month state transition UAT signed
- [ ] Frontend operational UAT (not just page navigation)

## Final release risk grade
**HIGH RISK / NOT READY FOR FULL PARITY CUTOVER**
