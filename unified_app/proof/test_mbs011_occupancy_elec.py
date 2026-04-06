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
    spec = importlib.util.spec_from_file_location('mbs_unified_app', str(APP_PATH))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


class TestMBS011OccupancyElec(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.NamedTemporaryFile(suffix='.db', delete=False)
        self.db_path = self.tmp.name
        self.tmp.close()
        self.bootstrap_min_schema(self.db_path)
        self.mod = load_app(self.db_path)
        self.client = self.mod.app.test_client()

    def tearDown(self):
        try:
            os.unlink(self.db_path)
        except FileNotFoundError:
            pass

    def bootstrap_min_schema(self, db_path):
        con = sqlite3.connect(db_path)
        cur = con.cursor()
        cur.executescript('''
            CREATE TABLE "Employees_Master" (
              "CompanyID" TEXT PRIMARY KEY,
              "Name" TEXT NOT NULL,
              "CNIC_No." TEXT NOT NULL,
              "Department" TEXT NOT NULL,
              "Designation" TEXT NOT NULL,
              "Unit_ID" TEXT NOT NULL,
              "Active" TEXT NOT NULL DEFAULT 'Yes'
            );
            CREATE TABLE util_rate_monthly (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL UNIQUE,
              elec_rate NUMERIC(12,4) NOT NULL,
              water_general_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              water_drinking_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              school_van_rate NUMERIC(12,2) NOT NULL DEFAULT 0
            );
            CREATE TABLE util_billing_run (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL,
              run_key TEXT NOT NULL,
              run_status TEXT NOT NULL,
              started_by_user_id INTEGER NULL,
              UNIQUE(month_cycle, run_key)
            );
            CREATE TABLE util_billing_line (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              billing_run_id INTEGER NOT NULL,
              month_cycle TEXT NOT NULL,
              employee_id TEXT NOT NULL,
              utility_type TEXT NOT NULL,
              qty NUMERIC(14,4) NOT NULL DEFAULT 0,
              rate NUMERIC(14,4) NOT NULL DEFAULT 0,
              amount NUMERIC(14,2) NOT NULL DEFAULT 0,
              source_ref TEXT NULL,
              UNIQUE(billing_run_id, employee_id, utility_type)
            );
            CREATE TABLE util_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL,
              employee_id TEXT NOT NULL,
              chargeable_general_water_liters NUMERIC(14,4) NOT NULL DEFAULT 0,
              water_general_amount NUMERIC(14,2) NOT NULL DEFAULT 0,
              elec_units NUMERIC(14,4) NOT NULL DEFAULT 0,
              elec_amount NUMERIC(14,2) NOT NULL DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            CREATE TABLE util_drinking_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL,
              employee_id TEXT NOT NULL,
              billed_liters NUMERIC(14,4) NOT NULL DEFAULT 0,
              rate NUMERIC(14,4) NOT NULL DEFAULT 0,
              amount NUMERIC(14,2) NOT NULL DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            CREATE TABLE util_school_van_monthly_charge (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL,
              employee_id TEXT NOT NULL,
              amount NUMERIC(14,2) NOT NULL DEFAULT 0
            );
            CREATE TABLE util_month_cycle (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL UNIQUE,
              state TEXT NOT NULL DEFAULT 'OPEN'
            );
        ''')
        con.commit()
        con.close()

    def seed_common(self):
        c = self.client
        # employees
        con = sqlite3.connect(self.db_path)
        cur = con.cursor()
        cur.execute("INSERT INTO \"Employees_Master\"(\"CompanyID\",\"Name\",\"CNIC_No.\",\"Department\",\"Designation\",\"Unit_ID\",\"Active\") VALUES('E1','Emp 1','111','Ops','Tech','U1','Yes')")
        cur.execute("INSERT INTO \"Employees_Master\"(\"CompanyID\",\"Name\",\"CNIC_No.\",\"Department\",\"Designation\",\"Unit_ID\",\"Active\") VALUES('E2','Emp 2','222','Ops','Tech','U1','Yes')")
        cur.execute("INSERT INTO \"Employees_Master\"(\"CompanyID\",\"Name\",\"CNIC_No.\",\"Department\",\"Designation\",\"Unit_ID\",\"Active\") VALUES('E3','Emp 3','333','Ops','Tech','U1','Yes')")
        cur.execute("INSERT INTO util_rate_monthly(month_cycle,elec_rate,water_general_rate,water_drinking_rate,school_van_rate) VALUES('Jan-2026',10,0,0,0)")
        con.commit(); con.close()

        # rooms snapshot U1 = 2 rooms Family A (free = 600)
        c.post('/rooms/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R1'})
        c.post('/rooms/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R2'})

    def test_case1_usage_less_than_free(self):
        self.seed_common()
        c = self.client

        # usage 500 < free 2*300=600
        c.post('/meter-unit/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'meter_units': 500})

        c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R1', 'unit_id': 'U1', 'employee_id': 'E1', 'active_days': 20})
        c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R2', 'unit_id': 'U1', 'employee_id': 'E2', 'active_days': 10})

        r = c.post('/billing/elec/compute', json={'month_cycle': 'Jan-2026'})
        self.assertEqual(r.status_code, 200)

        summary = c.get('/reports/elec-summary?month_cycle=Jan-2026').get_json()
        self.assertEqual(float(summary['units'][0]['net_units']), 0.0)
        for s in summary['employee_shares']:
            self.assertEqual(float(s['share_amount']), 0.0)

    def test_case2_totals_match_with_rounding_fix(self):
        self.seed_common()
        c = self.client
        c.post('/meter-unit/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'meter_units': 700})  # net=100, amount=1000

        c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R1', 'unit_id': 'U1', 'employee_id': 'E1', 'active_days': 1})
        c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R1', 'unit_id': 'U1', 'employee_id': 'E2', 'active_days': 1})
        c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R2', 'unit_id': 'U1', 'employee_id': 'E3', 'active_days': 1})

        c.post('/billing/elec/compute', json={'month_cycle': 'Jan-2026'})
        out = c.get('/reports/elec-summary?month_cycle=Jan-2026').get_json()

        unit_amount = float(out['units'][0]['unit_amount'])
        summed = round(sum(float(x['share_amount']) for x in out['employee_shares']), 2)
        self.assertEqual(unit_amount, summed)

    def test_case3_uniqueness_month_employee(self):
        self.seed_common()
        c = self.client
        r1 = c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R1', 'unit_id': 'U1', 'employee_id': 'E1', 'active_days': 10})
        self.assertEqual(r1.status_code, 200)

        # second insert should update same unique key, not duplicate
        r2 = c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R2', 'unit_id': 'U1', 'employee_id': 'E1', 'active_days': 12})
        self.assertEqual(r2.status_code, 200)

        rows = c.get('/occupancy?month_cycle=Jan-2026').get_json()['rows']
        self.assertEqual(len([x for x in rows if x['employee_id'] == 'E1']), 1)
        self.assertEqual(int(rows[0]['active_days']), 12)

    def test_case4_rooms_count_from_rooms_snapshot_only(self):
        self.seed_common()
        c = self.client

        # occupancy only one room used, but rooms snapshot has 2 rooms -> free must use 2*300
        c.post('/meter-unit/upsert', json={'month_cycle': 'Jan-2026', 'unit_id': 'U1', 'meter_units': 650})
        c.post('/occupancy/upsert', json={'month_cycle': 'Jan-2026', 'category': 'Family A', 'block_floor': 'B1', 'room_no': 'R1', 'unit_id': 'U1', 'employee_id': 'E1', 'active_days': 10})

        c.post('/billing/elec/compute', json={'month_cycle': 'Jan-2026'})
        out = c.get('/reports/elec-summary?month_cycle=Jan-2026').get_json()

        unit = out['units'][0]
        self.assertEqual(int(unit['rooms_count']), 2)
        self.assertEqual(float(unit['unit_free_units']), 600.0)
        self.assertEqual(float(unit['net_units']), 50.0)


if __name__ == '__main__':
    unittest.main()
