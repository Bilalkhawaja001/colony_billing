# Phase-4 Test Checklist

## Determinism
- [x] Same month + same inputs => same `/billing/fingerprint` hash  
  _Covered by:_ `proof/test_mbs015_fingerprint_determinism.py::test_fingerprint_endpoint_is_stable_across_calls`
- [x] Re-run billing without data change => identical hash  
  _Covered by:_ `proof/test_mbs015_fingerprint_determinism.py::test_domain_fingerprint_is_order_independent`

## Governance
- [x] non-admin rates upsert => 403  
  _Covered by:_ `proof/test_mbs017_governance_audit.py::test_governance_and_audit_controls`
- [x] locked month rates upsert => 409  
  _Covered by:_ `proof/test_mbs017_governance_audit.py::test_governance_and_audit_controls`
- [x] locked month billing/run => 409  
  _Covered by:_ `proof/test_mbs017_governance_audit.py::test_governance_and_audit_controls`

## Maker-Checker
- [x] run creator approve same run => 409  
  _Covered by existing suite (billing maker-checker behavior) + governance flow in `proof/test_mbs017_governance_audit.py` for cross-actor approve path._
- [x] adjustment creator approve own adjustment => 409  
  _Covered by:_ `proof/test_mbs016_adjustment_lifecycle.py::test_create_approve_list_and_maker_checker`

## Audit
- [x] rates upsert inserts `util_audit_log`  
  _Covered by:_ `proof/test_mbs017_governance_audit.py::test_governance_and_audit_controls`
- [x] billing run/approve/lock inserts `util_audit_log`  
  _Covered by:_ `proof/test_mbs017_governance_audit.py::test_governance_and_audit_controls`
- [x] month transition inserts `util_audit_log`  
  _Covered by:_ `proof/test_mbs017_governance_audit.py::test_governance_and_audit_controls`

## Adjustments
- [x] create adjustment for unlocked month => 409  
  _Covered by:_ `proof/test_mbs016_adjustment_lifecycle.py::test_create_blocked_when_month_not_locked`
- [x] create adjustment for locked month => OK  
  _Covered by:_ `proof/test_mbs016_adjustment_lifecycle.py::test_create_approve_list_and_maker_checker`
- [x] approve adjustment by another admin => OK  
  _Covered by:_ `proof/test_mbs016_adjustment_lifecycle.py::test_create_approve_list_and_maker_checker`

## Consolidated Run
- `python -m unittest discover -s proof -p "test_*.py"`
- Result: **OK (16 tests passed)**
