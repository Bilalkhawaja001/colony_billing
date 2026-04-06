import unittest
from decimal import Decimal
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
API_DIR = ROOT / 'api'
if str(API_DIR) not in sys.path:
    sys.path.insert(0, str(API_DIR))

from domain import billing_engine as eng


class TestColonyBillingEngine(unittest.TestCase):
    def test_duplicate_hr_row_stops(self):
        out = eng.run_colony_billing(
            '12-2025',
            unit_rows=[],
            hr_rows=[{'company_id': 'C1', 'active_days': 10}, {'company_id': 'C1', 'active_days': 20}],
            map_rows=[],
            ro_rows=[]
        )
        self.assertTrue(out['stop'])
        self.assertTrue(any(x['code'] == 'DUP_HR' for x in out['logs']))

    def test_ghost_tenant_penalty(self):
        out = eng.run_colony_billing(
            '12-2025',
            unit_rows=[{'unit_id': 'U1', 'meter_type': 'water', 'usage': 10, 'amount': 100}],
            hr_rows=[],
            map_rows=[{'unit_id': 'U1', 'company_id': 'C9'}],
            ro_rows=[]
        )
        self.assertFalse(out['stop'])
        self.assertTrue(any(x['code'] == 'GHOST_TENANT_PENALTY' for x in out['logs']))

    def test_single_occupant_bypass(self):
        out = eng.run_colony_billing(
            '12-2025',
            unit_rows=[{'unit_id': 'U1', 'meter_type': 'water', 'usage': 10, 'amount': 60}, {'unit_id': 'U1', 'meter_type': 'power', 'usage': 10, 'amount': 40}],
            hr_rows=[{'company_id': 'C1', 'active_days': 0}],
            map_rows=[{'unit_id': 'U1', 'company_id': 'C1'}],
            ro_rows=[]
        )
        self.assertFalse(out['stop'])
        self.assertEqual(len(out['billing_rows']), 1)
        self.assertEqual(out['billing_rows'][0]['total_amt'], Decimal('100.00'))

    def test_zero_unit_days_no_bill(self):
        out = eng.run_colony_billing(
            '12-2025',
            unit_rows=[{'unit_id': 'U1', 'meter_type': 'water', 'usage': 10, 'amount': 50}],
            hr_rows=[{'company_id': 'C1', 'active_days': 0}, {'company_id': 'C2', 'active_days': 0}],
            map_rows=[{'unit_id': 'U1', 'company_id': 'C1'}, {'unit_id': 'U1', 'company_id': 'C2'}],
            ro_rows=[]
        )
        self.assertTrue(any(x['code'] == 'UNIT_ZERO_DAYS' for x in out['logs']))
        self.assertEqual(len(out['billing_rows']), 0)

    def test_reconciliation_pass(self):
        out = eng.run_colony_billing(
            '12-2025',
            unit_rows=[{'unit_id': 'U1', 'meter_type': 'water', 'usage': 10, 'amount': 100}],
            hr_rows=[{'company_id': 'C1', 'active_days': 10}, {'company_id': 'C2', 'active_days': 20}],
            map_rows=[{'unit_id': 'U1', 'company_id': 'C1'}, {'unit_id': 'U1', 'company_id': 'C2'}],
            ro_rows=[]
        )
        self.assertFalse(out['stop'])
        self.assertFalse(any(x['code'] == 'RECON_FAIL' for x in out['logs']))


if __name__ == '__main__':
    unittest.main()
