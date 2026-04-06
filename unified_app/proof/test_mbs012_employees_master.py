import os
import sqlite3
import tempfile
import unittest
import importlib.util
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
APP_PATH = ROOT / 'api' / 'app.py'


def load_app(db_path: str):
    os.environ['MBS_DB_PATH'] = db_path
    spec = importlib.util.spec_from_file_location('mbs_unified_app2', str(APP_PATH))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


class TestMBS012EmployeesMaster(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.NamedTemporaryFile(suffix='.db', delete=False)
        self.db_path = self.tmp.name
        self.tmp.close()
        self.bootstrap(self.db_path)
        self.mod = load_app(self.db_path)
        self.client = self.mod.app.test_client()

    def tearDown(self):
        try:
            os.unlink(self.db_path)
        except FileNotFoundError:
            pass

    def bootstrap(self, db_path):
        con = sqlite3.connect(db_path)
        cur = con.cursor()
        cur.executescript('''
            CREATE TABLE util_unit (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              unit_id TEXT NOT NULL UNIQUE
            );
            INSERT INTO util_unit(unit_id) VALUES('U1');

            CREATE TABLE util_rate_monthly (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL UNIQUE,
              elec_rate NUMERIC(12,4) NOT NULL,
              water_general_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              water_drinking_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              school_van_rate NUMERIC(12,2) NOT NULL DEFAULT 0
            );
            CREATE TABLE util_billing_run (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, run_key TEXT, run_status TEXT, started_by_user_id INTEGER, UNIQUE(month_cycle,run_key));
            CREATE TABLE util_billing_line (id INTEGER PRIMARY KEY AUTOINCREMENT, billing_run_id INTEGER, month_cycle TEXT, employee_id TEXT, utility_type TEXT, qty NUMERIC, rate NUMERIC, amount NUMERIC, source_ref TEXT, UNIQUE(billing_run_id,employee_id,utility_type));
            CREATE TABLE util_formula_result (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, chargeable_general_water_liters NUMERIC DEFAULT 0, water_general_amount NUMERIC DEFAULT 0, elec_units NUMERIC DEFAULT 0, elec_amount NUMERIC DEFAULT 0, UNIQUE(month_cycle,employee_id));
            CREATE TABLE util_drinking_formula_result (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, billed_liters NUMERIC DEFAULT 0, rate NUMERIC DEFAULT 0, amount NUMERIC DEFAULT 0, UNIQUE(month_cycle,employee_id));
            CREATE TABLE util_school_van_monthly_charge (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, amount NUMERIC DEFAULT 0);
            CREATE TABLE util_month_cycle (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT UNIQUE, state TEXT);
        ''')
        con.commit(); con.close()

    def test_manual_add_visible_in_occupancy_and_duplicate_blocked(self):
        c = self.client
        r = c.post('/rooms/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'category': 'Family A', 'block_floor': 'B', 'room_no': 'R1'})
        self.assertEqual(r.status_code, 200)

        payload = {
            'CompanyID': 'C001', 'Name': 'Ali', 'CNIC_No.': '12345', 'Department': 'Ops', 'Designation': 'Tech', 'Unit_ID': 'U1', 'Active': 'Yes'
        }
        r1 = c.post('/employees/add', json=payload)
        self.assertEqual(r1.status_code, 200)

        # duplicate blocked
        r2 = c.post('/employees/add', json=payload)
        self.assertEqual(r2.status_code, 409)

        # immediately usable in occupancy
        r3 = c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B', 'room_no': 'R1', 'unit_id': 'U1', 'CompanyID': 'C001', 'active_days': 25})
        self.assertEqual(r3.status_code, 200)

    def test_inactive_not_allowed_in_occupancy(self):
        c = self.client
        c.post('/rooms/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'category': 'Family A', 'block_floor': 'B', 'room_no': 'R1'})
        c.post('/employees/add', json={'CompanyID': 'C002', 'Name': 'Inactive', 'CNIC_No.': '999', 'Department': 'Ops', 'Designation': 'Tech', 'Unit_ID': 'U1', 'Active': 'No'})

        r = c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B', 'room_no': 'R1', 'unit_id': 'U1', 'CompanyID': 'C002', 'active_days': 10})
        self.assertEqual(r.status_code, 400)


if __name__ == '__main__':
    unittest.main()
