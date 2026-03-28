# UPDATED_RELEASE_CHECKLIST

## Evidence-gated checklist

### A. Bootstrap and regression
- [x] Fresh bootstrap migration passes with explicit PHP binary
- [x] Critical non-EV1 regression suite passes (44/44)
- [x] Removed 410 policy locked by tests

### B. Golden-month parity gate (required for LIMITED GO proof)
- [x] Flask golden run executed
- [x] Laravel golden run executed on fresh bootstrap
- [x] Raw persisted line dump compared
- [x] Persisted output parity matched for Diff-001 (3 lines, ELEC absent)
- [x] Summary parity for limited persisted scope matched

### C. Scope integrity
- [x] EV1 untouched
- [x] shell pages marked non-proof surfaces
- [x] removed flows remain excluded

## Release status from checklist
- Full GO: **Not allowed**
- LIMITED GO (proven): **Yes, for constrained non-EV1 scope defined in scope note**
- Current practical verdict: **LIMITED GO**