import os
import sqlite3
import tempfile
import unittest
import importlib.util
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
APP_PATH = ROOT / 'api' / 'app.py'


def load_app(db_path: str):
    os.environ['MBS_DB_PATH'] = db_path
    os.environ['MBS_ADMIN_USER_IDS'] = '1,2'
    api_dir = str(ROOT / 'api')
    if api_dir not in sys.path:
        sys.path.insert(0, api_dir)
    spec = importlib.util.spec_from_file_location('mbs_unified_app16', str(APP_PATH))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


class TestMBS016AdjustmentLifecycle(unittest.TestCase):
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
            CREATE TABLE util_month_cycle (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT UNIQUE,
              state TEXT
            );
            INSERT INTO util_month_cycle(month_cycle, state) VALUES('Mar-2026', 'LOCKED');
            INSERT INTO util_month_cycle(month_cycle, state) VALUES('Apr-2026', 'OPEN');

            CREATE TABLE util_billing_run (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              run_key TEXT,
              run_status TEXT,
              started_by_user_id INTEGER,
              UNIQUE(month_cycle, run_key)
            );
            CREATE TABLE util_billing_line (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              billing_run_id INTEGER,
              month_cycle TEXT,
              employee_id TEXT,
              utility_type TEXT,
              qty NUMERIC,
              rate NUMERIC,
              amount NUMERIC,
              source_ref TEXT,
              UNIQUE(billing_run_id, employee_id, utility_type)
            );
            CREATE TABLE util_rate_monthly (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL UNIQUE,
              elec_rate NUMERIC(12,4) NOT NULL,
              water_general_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              water_drinking_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              school_van_rate NUMERIC(12,2) NOT NULL DEFAULT 0
            );
            CREATE TABLE util_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              chargeable_general_water_liters NUMERIC DEFAULT 0,
              water_general_amount NUMERIC DEFAULT 0,
              elec_units NUMERIC DEFAULT 0,
              elec_amount NUMERIC DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            CREATE TABLE util_drinking_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              billed_liters NUMERIC DEFAULT 0,
              rate NUMERIC DEFAULT 0,
              amount NUMERIC DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            CREATE TABLE util_school_van_monthly_charge (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              amount NUMERIC DEFAULT 0
            );
        ''')
        con.commit()
        con.close()

    def test_create_approve_list_and_maker_checker(self):
        c = self.client

        r_create = c.post('/billing/adjustments/create', json={
            'actor_user_id': '1',
            'month_cycle': 'Mar-2026',
            'employee_id': 'E001',
            'utility_type': 'ELECTRICITY',
            'amount_delta': 120.5,
            'reason': 'Post-lock correction'
        })
        self.assertEqual(r_create.status_code, 200)
        adj_id = r_create.get_json()['adjustment_id']

        r_same_actor_approve = c.post('/billing/adjustments/approve', json={
            'actor_user_id': '1',
            'adjustment_id': adj_id
        })
        self.assertEqual(r_same_actor_approve.status_code, 409)
        self.assertIn('maker-checker', r_same_actor_approve.get_json().get('error', '').lower())

        r_approve = c.post('/billing/adjustments/approve', json={
            'actor_user_id': '2',
            'adjustment_id': adj_id
        })
        self.assertEqual(r_approve.status_code, 200)

        r_list = c.get('/billing/adjustments/list', query_string={
            'month_cycle': 'Mar-2026',
            'employee_id': 'E001'
        })
        self.assertEqual(r_list.status_code, 200)
        rows = r_list.get_json()['rows']
        self.assertTrue(len(rows) >= 1)
        self.assertEqual(rows[0]['status'], 'APPROVED')

    def test_create_blocked_when_month_not_locked(self):
        r = self.client.post('/billing/adjustments/create', json={
            'actor_user_id': '1',
            'month_cycle': 'Apr-2026',
            'employee_id': 'E099',
            'utility_type': 'WATER_GENERAL',
            'amount_delta': 50,
            'reason': 'Should fail in OPEN month'
        })
        self.assertEqual(r.status_code, 409)
        self.assertIn('LOCKED', r.get_json().get('error', ''))


if __name__ == '__main__':
    unittest.main()
